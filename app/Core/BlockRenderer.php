<?php

declare(strict_types=1);

namespace HuberCMS\Core;

/**
 * BlockRenderer
 *
 * Converts HuberCMS block JSON (from the block editor) into frontend HTML.
 * Each block type has a dedicated render method.
 *
 * @package HuberCMS\Core
 */
final class BlockRenderer
{
    /**
     * Render a block JSON string (or array) to HTML.
     */
    public static function render(string|array $content): string
    {
        if (is_string($content)) {
            $trimmed = trim($content);

            // Not block JSON → treat as plain HTML / Markdown output
            if ($trimmed === '' || $trimmed[0] !== '[') {
                return '<div class="hp-content">' . nl2br(htmlspecialchars($trimmed, ENT_QUOTES)) . '</div>';
            }

            $blocks = json_decode($trimmed, true);
            if (!is_array($blocks)) {
                return '<div class="hp-content">' . nl2br(htmlspecialchars($trimmed, ENT_QUOTES)) . '</div>';
            }
        } else {
            $blocks = $content;
        }

        $html = '';
        foreach ($blocks as $block) {
            $type  = $block['type']  ?? '';
            $attrs = $block['attrs'] ?? [];
            $html .= self::renderBlock($type, $attrs);
        }

        return $html;
    }

    // =========================================================
    // Block dispatcher
    // =========================================================

    private static function renderBlock(string $type, array $attrs): string
    {
        return match ($type) {
            'paragraph' => self::renderParagraph($attrs),
            'heading'   => self::renderHeading($attrs),
            'image'     => self::renderImage($attrs),
            'gallery'   => self::renderGallery($attrs),
            'quote'     => self::renderQuote($attrs),
            'list'      => self::renderList($attrs),
            'separator' => self::renderSeparator($attrs),
            'button'    => self::renderButton($attrs),
            'columns'   => self::renderColumns($attrs),
            'code'      => self::renderCode($attrs),
            'callout'   => self::renderCallout($attrs),
            'video'     => self::renderVideo($attrs),
            'html'      => self::renderHtml($attrs),
            'table'     => self::renderTable($attrs),
            'spacer'    => self::renderSpacer($attrs),
            default     => '',
        };
    }

    // =========================================================
    // Block renderers
    // =========================================================

    /** 1. Paragraph */
    private static function renderParagraph(array $a): string
    {
        $align   = self::safeAlign($a['align'] ?? 'left');
        $content = $a['content'] ?? '';
        return "<p class=\"hp-block hp-block-paragraph\" style=\"text-align:{$align}\">{$content}</p>\n";
    }

    /** 2. Heading */
    private static function renderHeading(array $a): string
    {
        $level   = max(1, min(6, (int) ($a['level'] ?? 2)));
        $align   = self::safeAlign($a['align'] ?? 'left');
        $content = $a['content'] ?? '';
        $id      = 'h-' . substr(preg_replace('/[^a-z0-9]+/', '-', strtolower(strip_tags($content))), 0, 60);
        return "<h{$level} id=\"{$id}\" class=\"hp-block hp-block-heading\" style=\"text-align:{$align}\">{$content}</h{$level}>\n";
    }

    /** 3. Image */
    private static function renderImage(array $a): string
    {
        $url     = htmlspecialchars($a['url'] ?? '', ENT_QUOTES);
        $alt     = htmlspecialchars($a['alt'] ?? '', ENT_QUOTES);
        $caption = $a['caption'] ?? '';
        $align   = self::safeAlign($a['align'] ?? 'center');
        $width   = self::safeWidth($a['width'] ?? '100%');

        if (!$url) return '';

        $img = "<figure class=\"hp-block hp-block-image\" style=\"text-align:{$align}\">\n"
             . "  <img src=\"{$url}\" alt=\"{$alt}\" style=\"max-width:{$width};height:auto;border-radius:8px\">\n"
             . ($caption ? "  <figcaption>{$caption}</figcaption>\n" : '')
             . "</figure>\n";

        return $img;
    }

    /** 4. Gallery */
    private static function renderGallery(array $a): string
    {
        $images = array_filter($a['images'] ?? []);
        if (!$images) return '';

        $cols = max(2, min(4, (int) ($a['columns'] ?? 3)));
        $items = '';
        foreach ($images as $url) {
            $url    = htmlspecialchars((string) $url, ENT_QUOTES);
            $items .= "  <li class=\"hp-gallery-item\"><a href=\"{$url}\" target=\"_blank\"><img src=\"{$url}\" alt=\"\" loading=\"lazy\"></a></li>\n";
        }

        return "<figure class=\"hp-block hp-block-gallery\">\n"
             . "  <ul class=\"hp-gallery-grid\" style=\"--hp-gallery-cols:{$cols}\">\n"
             . $items
             . "  </ul>\n</figure>\n";
    }

    /** 5. Quote */
    private static function renderQuote(array $a): string
    {
        $content = $a['content'] ?? '';
        $author  = htmlspecialchars($a['author'] ?? '', ENT_QUOTES);

        return "<blockquote class=\"hp-block hp-block-quote\">\n"
             . "  <p>{$content}</p>\n"
             . ($author ? "  <cite>— {$author}</cite>\n" : '')
             . "</blockquote>\n";
    }

    /** 6. List */
    private static function renderList(array $a): string
    {
        $items   = array_filter($a['items'] ?? []);
        $ordered = !empty($a['ordered']);
        $tag     = $ordered ? 'ol' : 'ul';

        if (!$items) return '';

        $li = implode('', array_map(
            fn($i) => '  <li>' . htmlspecialchars((string) $i, ENT_QUOTES) . "</li>\n",
            $items
        ));

        return "<{$tag} class=\"hp-block hp-block-list\">\n{$li}</{$tag}>\n";
    }

    /** 7. Separator */
    private static function renderSeparator(array $a): string
    {
        $style = in_array($a['style'] ?? '', ['solid','dashed','dotted']) ? $a['style'] : 'solid';
        $width = self::safeWidth($a['width'] ?? '100%');
        $color = self::safeColor($a['color'] ?? '#334155');

        return "<hr class=\"hp-block hp-block-separator\" "
             . "style=\"border:none;border-top:2px {$style} {$color};width:{$width};margin:1.5rem auto\">\n";
    }

    /** 8. Button */
    private static function renderButton(array $a): string
    {
        $buttons = $a['buttons'] ?? [];
        if (!$buttons) return '';

        $btnMap = [
            'primary'   => 'background:var(--hp-primary,#6366f1);color:#fff',
            'secondary' => 'background:transparent;border:2px solid var(--hp-primary,#6366f1);color:var(--hp-primary,#6366f1)',
            'outline'   => 'background:transparent;border:2px solid currentColor;color:inherit',
        ];

        $items = '';
        foreach ($buttons as $btn) {
            $text   = htmlspecialchars($btn['text'] ?? 'Button', ENT_QUOTES);
            $url    = htmlspecialchars($btn['url']  ?? '#', ENT_QUOTES);
            $style  = $btn['style'] ?? 'primary';
            $css    = $btnMap[$style] ?? $btnMap['primary'];
            $target = ($btn['target'] ?? '') === '_blank' ? ' target="_blank" rel="noopener"' : '';

            $items .= "  <a href=\"{$url}\" class=\"hp-btn hp-btn-{$style}\"{$target} "
                    . "style=\"{$css};display:inline-flex;align-items:center;padding:.6rem 1.5rem;border-radius:9999px;font-weight:600;text-decoration:none;transition:opacity .2s\">"
                    . "{$text}</a>\n";
        }

        return "<div class=\"hp-block hp-block-buttons\" style=\"display:flex;flex-wrap:wrap;gap:.75rem\">\n{$items}</div>\n";
    }

    /** 9. Columns */
    private static function renderColumns(array $a): string
    {
        $count = max(2, min(3, (int) ($a['count'] ?? 2)));
        $cols  = $a['cols'] ?? [];

        $cells = '';
        for ($i = 0; $i < $count; $i++) {
            $cells .= "  <div class=\"hp-col\">" . ($cols[$i] ?? '') . "</div>\n";
        }

        return "<div class=\"hp-block hp-block-columns hp-columns-{$count}\" "
             . "style=\"display:grid;grid-template-columns:repeat({$count},1fr);gap:1.5rem\">\n"
             . $cells . "</div>\n";
    }

    /** 10. Code */
    private static function renderCode(array $a): string
    {
        $lang    = htmlspecialchars($a['language'] ?? 'text', ENT_QUOTES);
        $content = htmlspecialchars($a['content'] ?? '', ENT_QUOTES);

        return "<figure class=\"hp-block hp-block-code\">\n"
             . "  <pre><code class=\"language-{$lang}\">{$content}</code></pre>\n"
             . "</figure>\n";
    }

    /** 11. Callout */
    private static function renderCallout(array $a): string
    {
        $type    = in_array($a['type'] ?? '', ['info','success','warning','danger']) ? $a['type'] : 'info';
        $title   = htmlspecialchars($a['title'] ?? '', ENT_QUOTES);
        $content = $a['content'] ?? '';

        $iconMap = ['info'=>'ℹ️','success'=>'✅','warning'=>'⚠️','danger'=>'❌'];
        $icon    = $iconMap[$type];

        return "<div class=\"hp-block hp-block-callout hp-callout-{$type}\">\n"
             . "  <div class=\"hp-callout-icon\">{$icon}</div>\n"
             . "  <div class=\"hp-callout-body\">\n"
             . ($title ? "    <strong class=\"hp-callout-title\">{$title}</strong>\n" : '')
             . "    <div class=\"hp-callout-content\">{$content}</div>\n"
             . "  </div>\n</div>\n";
    }

    /** 12. Video */
    private static function renderVideo(array $a): string
    {
        $url     = $a['url'] ?? '';
        $caption = htmlspecialchars($a['caption'] ?? '', ENT_QUOTES);

        if (!$url) return '';

        // YouTube
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $m)) {
            $id = $m[1];
            return "<figure class=\"hp-block hp-block-video\">\n"
                 . "  <div style=\"position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:12px\">\n"
                 . "    <iframe src=\"https://www.youtube.com/embed/{$id}\" "
                 . "style=\"position:absolute;top:0;left:0;width:100%;height:100%;border:none\" "
                 . "allowfullscreen loading=\"lazy\"></iframe>\n"
                 . "  </div>\n"
                 . ($caption ? "  <figcaption>{$caption}</figcaption>\n" : '')
                 . "</figure>\n";
        }

        // Vimeo
        if (preg_match('/vimeo\.com\/(\d+)/', $url, $m)) {
            $id = $m[1];
            return "<figure class=\"hp-block hp-block-video\">\n"
                 . "  <div style=\"position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:12px\">\n"
                 . "    <iframe src=\"https://player.vimeo.com/video/{$id}\" "
                 . "style=\"position:absolute;top:0;left:0;width:100%;height:100%;border:none\" "
                 . "allowfullscreen loading=\"lazy\"></iframe>\n"
                 . "  </div>\n"
                 . ($caption ? "  <figcaption>{$caption}</figcaption>\n" : '')
                 . "</figure>\n";
        }

        // Fallback: video tag
        $safeUrl = htmlspecialchars($url, ENT_QUOTES);
        return "<figure class=\"hp-block hp-block-video\">\n"
             . "  <video controls style=\"width:100%;border-radius:12px\"><source src=\"{$safeUrl}\"></video>\n"
             . ($caption ? "  <figcaption>{$caption}</figcaption>\n" : '')
             . "</figure>\n";
    }

    /** 13. HTML */
    private static function renderHtml(array $a): string
    {
        $content = $a['content'] ?? '';
        return "<div class=\"hp-block hp-block-html\">\n{$content}\n</div>\n";
    }

    /** 14. Table */
    private static function renderTable(array $a): string
    {
        $head    = $a['head']    ?? [];
        $rows    = $a['rows']    ?? [];
        $hasHead = !empty($a['hasHead']) && !empty($head);

        if (!$rows && !$hasHead) return '';

        $html = "<div class=\"hp-block hp-block-table\" style=\"overflow-x:auto\">\n<table>\n";

        if ($hasHead) {
            $html .= "  <thead><tr>\n";
            foreach ($head as $cell) {
                $html .= '    <th>' . htmlspecialchars((string) $cell, ENT_QUOTES) . "</th>\n";
            }
            $html .= "  </tr></thead>\n";
        }

        $html .= "  <tbody>\n";
        foreach ($rows as $row) {
            $html .= "  <tr>\n";
            foreach ((array) $row as $cell) {
                $html .= '    <td>' . htmlspecialchars((string) $cell, ENT_QUOTES) . "</td>\n";
            }
            $html .= "  </tr>\n";
        }
        $html .= "  </tbody>\n</table>\n</div>\n";

        return $html;
    }

    /** 15. Spacer */
    private static function renderSpacer(array $a): string
    {
        $height = max(10, min(300, (int) ($a['height'] ?? 40)));
        return "<div class=\"hp-block hp-block-spacer\" style=\"height:{$height}px\" aria-hidden=\"true\"></div>\n";
    }

    // =========================================================
    // Security helpers
    // =========================================================

    private static function safeAlign(string $v): string
    {
        return in_array($v, ['left','center','right','justify']) ? $v : 'left';
    }

    private static function safeWidth(string $v): string
    {
        return preg_match('/^\d{1,3}%$/', $v) ? $v : '100%';
    }

    private static function safeColor(string $v): string
    {
        return preg_match('/^#[0-9a-fA-F]{3,6}$/', $v) ? $v : '#334155';
    }
}
