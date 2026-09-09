<?php

declare(strict_types=1);

namespace HuberCMS\Core;

use HuberCMS\Core\Database;
use HuberCMS\Core\QueryBuilder;
use InvalidArgumentException;

/**
 * Model
 *
 * Abstract base class for all HuberCMS models.
 * Provides:
 * - Static query builder factory (::query())
 * - CRUD helpers (find, findAll, create, save, delete)
 * - Automatic timestamp management (created_at, updated_at)
 * - Mass assignment protection via $fillable
 * - Attribute casting via $casts
 *
 * Each concrete model must define:
 *   protected static string $table = 'my_table';
 *
 * @package HuberCMS\Core
 */
abstract class Model
{
    /** Table name without prefix — override in child class */
    protected static string $table = '';

    /** Primary key column name */
    protected static string $primaryKey = 'id';

    /** Allowed mass-assignment columns — must be explicit */
    protected static array $fillable = [];

    /** Columns hidden from toArray() / JSON output */
    protected static array $hidden = ['password'];

    /** Type casts: column => type */
    protected static array $casts = [];

    /** Whether to auto-manage created_at / updated_at */
    protected static bool $timestamps = true;

    /** @var array<string, mixed> Loaded attributes */
    protected array $attributes = [];

    /** @var array<string, mixed> Original attributes (for dirty detection) */
    protected array $original = [];

    /** Whether this is an unsaved new instance */
    protected bool $exists = false;

    private static ?Database $dbInstance = null;

    // =========================================================
    // Constructor
    // =========================================================

    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    // =========================================================
    // Database injection (called by Application bootstrap)
    // =========================================================

    public static function setDatabase(Database $db): void
    {
        self::$dbInstance = $db;
    }

    protected static function db(): Database
    {
        if (self::$dbInstance === null) {
            throw new \RuntimeException('Database not set on Model. Did the Application boot?');
        }
        return self::$dbInstance;
    }

    // =========================================================
    // Query builder factory
    // =========================================================

    /**
     * Returns a new QueryBuilder scoped to this model's table.
     */
    public static function query(): QueryBuilder
    {
        return (new QueryBuilder(static::db()))->table(static::$table);
    }

    // =========================================================
    // Finders
    // =========================================================

    /**
     * Finds a row by primary key or returns null.
     *
     * @return static|null
     */
    public static function find(int|string $id): ?static
    {
        $row = static::query()->where(static::$primaryKey, $id)->first();
        return $row !== null ? static::hydrate($row) : null;
    }

    /**
     * Finds a row by primary key or throws an exception.
     *
     * @return static
     * @throws \RuntimeException
     */
    public static function findOrFail(int|string $id): static
    {
        $model = static::find($id);

        if ($model === null) {
            throw new \RuntimeException(
                sprintf('%s with %s = %s not found.', static::class, static::$primaryKey, $id)
            );
        }

        return $model;
    }

    /**
     * Returns all rows as model instances.
     *
     * @return static[]
     */
    public static function all(): array
    {
        return array_map(
            fn($row) => static::hydrate($row),
            static::query()->get()
        );
    }

    // =========================================================
    // Persistence
    // =========================================================

    /**
     * Creates a new row from fillable attributes.
     *
     * @param array<string, mixed> $attributes
     * @return static
     */
    public static function create(array $attributes): static
    {
        $model = new static($attributes);
        $model->save();
        return $model;
    }

    /**
     * Saves the model (INSERT or UPDATE depending on $exists).
     */
    public function save(): bool
    {
        if (static::$timestamps) {
            $now = date('Y-m-d H:i:s');
            if (!$this->exists) {
                $this->attributes['created_at'] = $now;
            }
            $this->attributes['updated_at'] = $now;
        }

        if ($this->exists) {
            static::query()
                ->where(static::$primaryKey, $this->getAttribute(static::$primaryKey))
                ->update($this->attributes);
        } else {
            $id = static::query()->insert($this->attributes);
            $this->attributes[static::$primaryKey] = $id;
            $this->exists = true;
        }

        $this->syncOriginal();
        return true;
    }

    /**
     * Deletes this model instance from the database.
     */
    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }

        static::query()
            ->where(static::$primaryKey, $this->getAttribute(static::$primaryKey))
            ->delete();

        $this->exists = false;
        return true;
    }

    // =========================================================
    // Attributes
    // =========================================================

    public function getAttribute(string $key): mixed
    {
        $value = $this->attributes[$key] ?? null;
        return isset(static::$casts[$key]) ? $this->castValue($value, static::$casts[$key]) : $value;
    }

    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function fill(array $attributes): void
    {
        foreach ($attributes as $key => $value) {
            if (empty(static::$fillable) || in_array($key, static::$fillable, true)) {
                $this->attributes[$key] = $value;
            }
        }
    }

    public function isDirty(string $key): bool
    {
        return ($this->attributes[$key] ?? null) !== ($this->original[$key] ?? null);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_diff_key($this->attributes, array_flip(static::$hidden));
    }

    // =========================================================
    // Magic
    // =========================================================

    public function __get(string $name): mixed
    {
        return $this->getAttribute($name);
    }

    public function __set(string $name, mixed $value): void
    {
        $this->setAttribute($name, $value);
    }

    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    // =========================================================
    // Private helpers
    // =========================================================

    /**
     * Hydrates a raw DB row into a model instance.
     *
     * @param array<string, mixed> $row
     * @return static
     */
    protected static function hydrate(array $row): static
    {
        $model = new static();
        $model->attributes = $row;
        $model->original   = $row;
        $model->exists     = true;
        return $model;
    }

    private function syncOriginal(): void
    {
        $this->original = $this->attributes;
    }

    private function castValue(mixed $value, string $cast): mixed
    {
        return match ($cast) {
            'int', 'integer' => (int) $value,
            'float', 'double' => (float) $value,
            'bool', 'boolean' => (bool) $value,
            'string' => (string) $value,
            'array'  => is_string($value) ? json_decode($value, true) : $value,
            'json'   => is_string($value) ? json_decode($value, true) : $value,
            'datetime' => $value !== null ? new \DateTimeImmutable((string) $value) : null,
            default  => $value,
        };
    }
}
