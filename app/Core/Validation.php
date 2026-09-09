<?php

declare(strict_types=1);

namespace HuberCMS\Core;

/**
 * Validation
 *
 * Validates input data against a set of rules.
 * Inspired by Laravel's Validator but completely custom.
 *
 * Supported rules:
 *   required, string, numeric, integer, email, min:n, max:n,
 *   minlength:n, maxlength:n, url, alpha, alphanumeric,
 *   in:a,b,c, not_in:a,b,c, regex:/pattern/, confirmed,
 *   unique:table,column (DB check), date, boolean, nullable
 *
 * Usage:
 *   $v = new Validation(['email' => 'required|email|maxlength:255']);
 *   $result = $v->validate($request->all());
 *   if (!$result->passes()) { ... $result->errors() ... }
 *
 * @package HuberCMS\Core
 */
final class Validation
{
    /** @var array<string, string|string[]> */
    private array $rules;

    /** @var array<string, string> Custom error messages */
    private array $messages;

    /** @var array<string, string[]> Validation errors */
    private array $errors = [];

    private ?Database $db = null;

    /**
     * @param array<string, string|string[]> $rules
     * @param array<string, string>          $messages  Custom error messages (key: 'field.rule')
     */
    public function __construct(array $rules, array $messages = [])
    {
        $this->rules    = $rules;
        $this->messages = $messages;
    }

    /**
     * Optional: inject DB for unique: rule checks.
     */
    public function withDatabase(Database $db): self
    {
        $this->db = $db;
        return $this;
    }

    /**
     * Runs all rules against the given data.
     *
     * @param array<string, mixed> $data
     */
    public function validate(array $data): self
    {
        $this->errors = [];

        foreach ($this->rules as $field => $ruleString) {
            $rules = is_array($ruleString) ? $ruleString : explode('|', $ruleString);
            $value = $data[$field] ?? null;

            $isNullable = in_array('nullable', $rules, true);

            if ($isNullable && ($value === null || $value === '')) {
                continue;
            }

            foreach ($rules as $rule) {
                if ($rule === 'nullable') {
                    continue;
                }

                $this->applyRule($field, $rule, $value, $data);
            }
        }

        return $this;
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    /**
     * @return array<string, string[]>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Returns the first error for a field, or null.
     */
    public function firstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Returns all errors flattened to a single array.
     *
     * @return string[]
     */
    public function allErrors(): array
    {
        return array_merge(...array_values($this->errors));
    }

    // =========================================================
    // Rule dispatcher
    // =========================================================

    private function applyRule(string $field, string $rule, mixed $value, array $data): void
    {
        [$name, $param] = $this->parseRule($rule);

        $passed = match ($name) {
            'required'    => $value !== null && $value !== '' && $value !== [],
            'string'      => is_string($value),
            'numeric'     => is_numeric($value),
            'integer'     => filter_var($value, FILTER_VALIDATE_INT) !== false,
            'boolean'     => in_array($value, [true, false, 1, 0, '1', '0', 'true', 'false'], true),
            'email'       => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            'url'         => filter_var($value, FILTER_VALIDATE_URL) !== false,
            'alpha'       => is_string($value) && ctype_alpha($value),
            'alphanumeric' => is_string($value) && ctype_alnum($value),
            'date'        => $value !== null && strtotime((string) $value) !== false,
            'min'         => is_numeric($value) && (float) $value >= (float) $param,
            'max'         => is_numeric($value) && (float) $value <= (float) $param,
            'minlength'   => is_string($value) && mb_strlen($value) >= (int) $param,
            'maxlength'   => is_string($value) && mb_strlen($value) <= (int) $param,
            'in'          => in_array($value, explode(',', (string) $param), true),
            'not_in'      => !in_array($value, explode(',', (string) $param), true),
            'regex'       => is_string($value) && preg_match($param ?? '', $value) === 1,
            'confirmed'   => $value === ($data["{$field}_confirmation"] ?? null),
            'unique'      => $this->validateUnique($value, $param ?? ''),
            default       => true, // Unknown rules pass silently
        };

        if (!$passed) {
            $this->addError($field, $name, $param);
        }
    }

    private function parseRule(string $rule): array
    {
        if (str_contains($rule, ':')) {
            [$name, $param] = explode(':', $rule, 2);
            return [$name, $param];
        }

        return [$rule, null];
    }

    private function validateUnique(mixed $value, string $param): bool
    {
        if ($this->db === null) {
            return true; // Skip if no DB available
        }

        [$table, $column] = explode(',', $param, 2) + ['', 'id'];
        $prefix = $this->db->prefix();
        $row = $this->db->selectOne(
            "SELECT COUNT(*) AS cnt FROM {$prefix}{$table} WHERE {$column} = ?",
            [$value]
        );

        return ($row['cnt'] ?? 0) == 0;
    }

    private function addError(string $field, string $rule, ?string $param): void
    {
        $customKey = "{$field}.{$rule}";
        if (isset($this->messages[$customKey])) {
            $this->errors[$field][] = $this->messages[$customKey];
            return;
        }

        $this->errors[$field][] = $this->defaultMessage($field, $rule, $param);
    }

    private function defaultMessage(string $field, string $rule, ?string $param): string
    {
        $label = ucfirst(str_replace('_', ' ', $field));
        return match ($rule) {
            'required'    => "{$label} ist erforderlich.",
            'email'       => "{$label} muss eine gültige E-Mail-Adresse sein.",
            'url'         => "{$label} muss eine gültige URL sein.",
            'string'      => "{$label} muss eine Zeichenkette sein.",
            'numeric'     => "{$label} muss eine Zahl sein.",
            'integer'     => "{$label} muss eine ganze Zahl sein.",
            'min'         => "{$label} muss mindestens {$param} sein.",
            'max'         => "{$label} darf maximal {$param} sein.",
            'minlength'   => "{$label} muss mindestens {$param} Zeichen lang sein.",
            'maxlength'   => "{$label} darf maximal {$param} Zeichen lang sein.",
            'in'          => "{$label} hat keinen gültigen Wert.",
            'confirmed'   => "{$label} stimmt nicht mit der Bestätigung überein.",
            'unique'      => "{$label} ist bereits vergeben.",
            'regex'       => "{$label} hat ein ungültiges Format.",
            default       => "{$label} ist ungültig.",
        };
    }
}
