<?php

declare(strict_types=1);

namespace HuberCMS\Core;

/**
 * TemplateEngine
 *
 * A lightweight custom template engine.
 * Compiles template directives to native PHP, then executes them.
 *
 * Supported syntax:
 *   {{ $var }}              — Echo with htmlspecialchars (XSS-safe)
 *   {!! $var !!}            — Echo raw (use with caution)
 *   @if ($cond) ... @endif
 *   @elseif ($cond)
 *   @else
 *   @foreach ($items as $item) ... @endforeach
 *   @for ($i=0;$i<10;$i++) ... @endfor
 *   @while ($cond) ... @endwhile
 *   @include('partial')
 *   @extends('layout')
 *   @section('name') ... @endsection
 *   @yield('name')
 *   @csrf                   — Hidden CSRF input field
 *   @method('PUT')          — Hidden _method field for method spoofing
 *   {{-- comment --}}       — Template comments (stripped)
 *
 * @package HuberCMS\Core
 */
final class TemplateEngine
{
    /** @var array<string, string> Registered sections from @section */
    private array $sections = [];

    /** @var string|null Parent layout declared with @extends */
    private ?string $extendsLayout = null;

    public function __construct(private readonly string $viewsPath)
    {
    }

    /**
     * Compiles and renders a template file with given data.
     *
     * @param array<string, mixed> $data
     */
    public function render(string $template, array $data = []): string
    {
        $filePath = $this->resolveTemplatePath($template);

        if (!file_exists($filePath)) {
            throw new \RuntimeException("Template [{$template}] not found at [{$filePath}].");
        }

        $source = (string) file_get_contents($filePath);
        $compiled = $this->compile($source);

        return $this->evaluate($compiled, $data);
    }

    // =========================================================
    // Compiler
    // =========================================================

    /**
     * Transforms template source into executable PHP.
     */
    private function compile(string $source): string
    {
        $source = $this->compileComments($source);
        $source = $this->compileExtends($source);
        $source = $this->compileSections($source);
        $source = $this->compileYields($source);
        $source = $this->compileIncludes($source);
        $source = $this->compileEchos($source);
        $source = $this->compileDirectives($source);

        return $source;
    }

    /** Strip {{-- comment --}} blocks */
    private function compileComments(string $s): string
    {
        return preg_replace('/\{\{--.*?--\}\}/s', '', $s);
    }

    /** @extends('layout') */
    private function compileExtends(string $s): string
    {
        return preg_replace_callback(
            "/@extends\(['\"]([^'\"]+)['\"]\)/",
            function ($m) {
                $this->extendsLayout = $m[1];
                return '';
            },
            $s
        );
    }

    /** @section('name') ... @endsection */
    private function compileSections(string $s): string
    {
        return preg_replace_callback(
            "/@section\(['\"]([^'\"]+)['\"]\)(.*?)@endsection/s",
            function ($m) {
                return "<?php \$this->startSection('{$m[1]}'); ?>{$m[2]}<?php \$this->endSection(); ?>";
            },
            $s
        );
    }

    /** @yield('name') */
    private function compileYields(string $s): string
    {
        return preg_replace(
            "/@yield\(['\"]([^'\"]+)['\"]\)/",
            "<?php echo \$this->yieldSection('$1'); ?>",
            $s
        );
    }

    /** @include('partial') */
    private function compileIncludes(string $s): string
    {
        return preg_replace_callback(
            "/@include\(['\"]([^'\"]+)['\"]\)/",
            fn($m) => "<?php echo \$this->renderInclude('{$m[1]}', get_defined_vars()); ?>",
            $s
        );
    }

    /** {{ $var }} and {!! $var !!} */
    private function compileEchos(string $s): string
    {
        // Raw: {!! $var !!}
        $s = preg_replace('/\{!!\s*(.+?)\s*!!\}/s', '<?php echo $1; ?>', $s);

        // Escaped: {{ $var }}
        $s = preg_replace(
            '/\{\{\s*(.+?)\s*\}\}/s',
            '<?php echo htmlspecialchars((string)($1), ENT_QUOTES, \'UTF-8\'); ?>',
            $s
        );

        return $s;
    }

    /** Control structures and helper directives */
    private function compileDirectives(string $s): string
    {
        // Greedy (.+) without /s: stops at last ')' on the line.
        // Non-greedy (.+?) would break on nested parens: @if (empty($x)) → if(empty($x: ← INVALID
        $replacements = [
            '/@if\s*\((.+)\)/'         => '<?php if($1): ?>',
            '/@elseif\s*\((.+)\)/'     => '<?php elseif($1): ?>',
            '/@else/'                   => '<?php else: ?>',
            '/@endif/'                  => '<?php endif; ?>',
            '/@foreach\s*\((.+)\)/'    => '<?php foreach($1): ?>',
            '/@endforeach/'             => '<?php endforeach; ?>',
            '/@for\s*\((.+)\)/'        => '<?php for($1): ?>',
            '/@endfor/'                 => '<?php endfor; ?>',
            '/@while\s*\((.+)\)/'      => '<?php while($1): ?>',
            '/@endwhile/'               => '<?php endwhile; ?>',
            '/@continue/'                 => '<?php continue; ?>',
            '/@break/'                    => '<?php break; ?>',
            '/@csrf/'                     => '<?php echo \'<input type="hidden" name="_token" value="\' . htmlspecialchars($__csrf ?? \'\', ENT_QUOTES) . \'">\'; ?>',
            '/@method\([\'"]([^\'"]+)[\'"]\)/' => '<?php echo \'<input type="hidden" name="_method" value="$1">\'; ?>',
            '/@php/'                      => '<?php',
            '/@endphp/'                   => '?>',
        ];

        foreach ($replacements as $pattern => $replacement) {
            $s = preg_replace($pattern, $replacement, $s);
        }

        return $s;
    }

    // =========================================================
    // Runtime helpers (called from compiled templates)
    // =========================================================

    public function startSection(string $name): void
    {
        ob_start();
        $this->sections['__current__'] = $name;
    }

    public function endSection(): void
    {
        $name = $this->sections['__current__'] ?? null;
        if ($name) {
            $this->sections[$name] = ob_get_clean();
            unset($this->sections['__current__']);
        }
    }

    public function yieldSection(string $name): string
    {
        return $this->sections[$name] ?? '';
    }

    /**
     * @param array<string, mixed> $data
     */
    public function renderInclude(string $template, array $data = []): string
    {
        return $this->render($template, $data);
    }

    // =========================================================
    // Private execution
    // =========================================================

    /**
     * Evaluates compiled PHP in an isolated scope with the given data.
     * The closure is NOT static so that $this (= TemplateEngine) is available
     * inside the eval'd template code for startSection(), yieldSection(), etc.
     *
     * @param array<string, mixed> $data
     */
    private function evaluate(string $compiled, array $data): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'hubercms_template_');
        if ($tempFile === false) {
            throw new \RuntimeException('Unable to create temporary template file.');
        }

        $content = "<?php\n"
            . "extract(\$data, EXTR_SKIP);\n"
            . "?>\n"
            . $compiled;

        try {
            if (file_put_contents($tempFile, $content, LOCK_EX) === false) {
                throw new \RuntimeException('Unable to write temporary template file.');
            }

            ob_start();
            $dataRef = $data;
            $_engine = $this;
            require $tempFile;
            $output = (string) ob_get_clean();
        } finally {
            @unlink($tempFile);
        }

        // After evaluating the child template, render the parent layout if @extends was used.
        if ($this->extendsLayout !== null) {
            $layout = $this->extendsLayout;
            $this->extendsLayout = null;
            $output = $this->render($layout, $data);
        }

        return $output;
    }

    /**
     * Resolves a template name like 'admin.dashboard' to a file path.
     */
    private function resolveTemplatePath(string $template): string
    {
        $relative = str_replace('.', DIRECTORY_SEPARATOR, $template) . '.php';
        return rtrim($this->viewsPath, '/\\') . DIRECTORY_SEPARATOR . $relative;
    }
}
