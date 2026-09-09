<?php

declare(strict_types=1);

namespace HuberCMS\Core;

use InvalidArgumentException;

/**
 * QueryBuilder
 *
 * A fluent SQL query builder that produces parameterized queries.
 * Wraps the Database class and prevents raw string interpolation.
 * Only supports MariaDB / MySQL syntax.
 *
 * Usage:
 *   $users = (new QueryBuilder($db))
 *       ->table('users')
 *       ->where('active', 1)
 *       ->orderBy('created_at', 'DESC')
 *       ->limit(10)
 *       ->get();
 *
 * @package HuberCMS\Core
 */
final class QueryBuilder
{
    private string $table = '';
    private string $alias = '';

    /** @var string[] */
    private array $selects = ['*'];

    /** @var array{sql:string, bindings:array}[] */
    private array $wheres = [];

    /** @var string[] */
    private array $joins = [];

    /** @var array<int|string, mixed> */
    private array $joinBindings = [];

    /** @var string[] */
    private array $orderBys = [];

    /** @var string[] */
    private array $groupBys = [];

    private ?string $havingSql = null;

    /** @var array<int|string, mixed> */
    private array $havingBindings = [];

    private ?int $limitValue = null;
    private ?int $offsetValue = null;
    private bool $distinct = false;

    public function __construct(private readonly Database $db)
    {
    }

    // =========================================================
    // Table & column selection
    // =========================================================

    public function table(string $table, string $alias = ''): self
    {
        $clone = clone $this;
        $clone->table = $this->db->prefix() . $table;
        $clone->alias = $alias;
        return $clone;
    }

    /** @param string|string[] $columns */
    public function select(string|array $columns): self
    {
        $clone = clone $this;
        $clone->selects = is_array($columns) ? $columns : [$columns];
        return $clone;
    }

    public function distinct(): self
    {
        $clone = clone $this;
        $clone->distinct = true;
        return $clone;
    }

    // =========================================================
    // WHERE clauses
    // =========================================================

    /**
     * Adds a WHERE condition with a binding.
     *
     * @param mixed $value
     */
    public function where(string $column, mixed $value, string $operator = '='): self
    {
        $this->assertSafeOperator($operator);
        $clone = clone $this;
        $clone->wheres[] = [
            'sql'      => "{$column} {$operator} ?",
            'bindings' => [$value],
        ];
        return $clone;
    }

    /**
     * Adds a WHERE IN condition.
     *
     * @param mixed[] $values
     */
    public function whereIn(string $column, array $values): self
    {
        if (empty($values)) {
            return $this->whereRaw('1 = 0');
        }

        $placeholders = implode(', ', array_fill(0, count($values), '?'));
        $clone = clone $this;
        $clone->wheres[] = [
            'sql'      => "{$column} IN ({$placeholders})",
            'bindings' => array_values($values),
        ];
        return $clone;
    }

    /**
     * Adds a WHERE NULL condition.
     */
    public function whereNull(string $column): self
    {
        $clone = clone $this;
        $clone->wheres[] = ['sql' => "{$column} IS NULL", 'bindings' => []];
        return $clone;
    }

    /**
     * Adds a WHERE NOT NULL condition.
     */
    public function whereNotNull(string $column): self
    {
        $clone = clone $this;
        $clone->wheres[] = ['sql' => "{$column} IS NOT NULL", 'bindings' => []];
        return $clone;
    }

    /**
     * Adds a raw WHERE clause (caller is responsible for safety).
     *
     * @param array<int|string, mixed> $bindings
     */
    public function whereRaw(string $sql, array $bindings = []): self
    {
        $clone = clone $this;
        $clone->wheres[] = ['sql' => $sql, 'bindings' => $bindings];
        return $clone;
    }

    // =========================================================
    // JOIN
    // =========================================================

    /**
     * @param array<int|string, mixed> $bindings
     */
    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER', array $bindings = []): self
    {
        $prefixed = $this->db->prefix() . $table;
        $clone = clone $this;
        $clone->joins[] = "{$type} JOIN {$prefixed} ON {$first} {$operator} {$second}";
        $clone->joinBindings = array_merge($clone->joinBindings, $bindings);
        return $clone;
    }

    public function leftJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    // =========================================================
    // ORDER, GROUP, LIMIT, OFFSET
    // =========================================================

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $clone = clone $this;
        $clone->orderBys[] = "{$column} {$direction}";
        return $clone;
    }

    public function groupBy(string $column): self
    {
        $clone = clone $this;
        $clone->groupBys[] = $column;
        return $clone;
    }

    public function limit(int $limit): self
    {
        $clone = clone $this;
        $clone->limitValue = max(0, $limit);
        return $clone;
    }

    public function offset(int $offset): self
    {
        $clone = clone $this;
        $clone->offsetValue = max(0, $offset);
        return $clone;
    }

    public function forPage(int $page, int $perPage = 15): self
    {
        return $this->offset(($page - 1) * $perPage)->limit($perPage);
    }

    // =========================================================
    // Execution
    // =========================================================

    /**
     * Fetches all matching rows.
     *
     * @return array<int, array<string, mixed>>
     */
    public function get(): array
    {
        [$sql, $bindings] = $this->buildSelect();
        return $this->db->select($sql, $bindings);
    }

    /**
     * Fetches the first matching row or null.
     *
     * @return array<string, mixed>|null
     */
    public function first(): ?array
    {
        [$sql, $bindings] = $this->buildSelect();
        return $this->db->selectOne($sql . ' LIMIT 1', $bindings);
    }

    /**
     * Returns the number of rows matching the WHERE conditions.
     */
    public function count(string $column = '*'): int
    {
        $clone = $this->select(["COUNT({$column}) AS aggregate"]);
        [$sql, $bindings] = $clone->buildSelect();
        $row = $this->db->selectOne($sql, $bindings);
        return (int) ($row['aggregate'] ?? 0);
    }

    /**
     * Inserts a row and returns the new ID.
     *
     * @param array<string, mixed> $data
     */
    public function insert(array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        return $this->db->insert($sql, array_values($data));
    }

    /**
     * Updates rows matching the WHERE conditions.
     *
     * @param array<string, mixed> $data
     */
    public function update(array $data): int
    {
        $setParts = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($data)));
        [$whereSQL, $whereBindings] = $this->buildWhere();

        $sql = "UPDATE {$this->table} SET {$setParts}{$whereSQL}";
        return $this->db->statement($sql, array_merge(array_values($data), $whereBindings));
    }

    /**
     * Deletes rows matching the WHERE conditions.
     */
    public function delete(): int
    {
        [$whereSQL, $whereBindings] = $this->buildWhere();
        $sql = "DELETE FROM {$this->table}{$whereSQL}";
        return $this->db->statement($sql, $whereBindings);
    }

    // =========================================================
    // Private builders
    // =========================================================

    /** @return array{0:string, 1:array} */
    private function buildSelect(): array
    {
        $distinct = $this->distinct ? 'DISTINCT ' : '';
        $columns = implode(', ', $this->selects);
        $tableExpr = $this->alias ? "{$this->table} AS {$this->alias}" : $this->table;

        $sql = "SELECT {$distinct}{$columns} FROM {$tableExpr}";
        $bindings = $this->joinBindings;

        foreach ($this->joins as $join) {
            $sql .= " {$join}";
        }

        [$whereSQL, $whereBindings] = $this->buildWhere();
        $sql .= $whereSQL;
        $bindings = array_merge($bindings, $whereBindings);

        if (!empty($this->groupBys)) {
            $sql .= ' GROUP BY ' . implode(', ', $this->groupBys);
        }

        if ($this->havingSql !== null) {
            $sql .= " HAVING {$this->havingSql}";
            $bindings = array_merge($bindings, $this->havingBindings);
        }

        if (!empty($this->orderBys)) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orderBys);
        }

        if ($this->limitValue !== null) {
            $sql .= " LIMIT {$this->limitValue}";
        }

        if ($this->offsetValue !== null) {
            $sql .= " OFFSET {$this->offsetValue}";
        }

        return [$sql, $bindings];
    }

    /** @return array{0:string, 1:array} */
    private function buildWhere(): array
    {
        if (empty($this->wheres)) {
            return ['', []];
        }

        $conditions = array_column($this->wheres, 'sql');
        $bindings = array_merge(...array_column($this->wheres, 'bindings'));

        return [' WHERE ' . implode(' AND ', $conditions), $bindings];
    }

    private function assertSafeOperator(string $op): void
    {
        $allowed = ['=', '!=', '<>', '<', '>', '<=', '>=', 'LIKE', 'NOT LIKE'];
        if (!in_array(strtoupper($op), $allowed, true)) {
            throw new InvalidArgumentException("SQL operator [{$op}] is not allowed.");
        }
    }
}
