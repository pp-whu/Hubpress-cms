<?php

declare(strict_types=1);

namespace HuberCMS\Core;

use PDO;
use PDOStatement;
use PDOException;
use RuntimeException;

/**
 * Database
 *
 * Wraps PDO with a clean API for prepared statements, transactions
 * and optional query logging.
 *
 * All queries MUST use parameter binding — never string interpolation.
 * This is the ONLY database access layer in HuberCMS.
 *
 * @package HuberCMS\Core
 */
final class Database
{
    private ?PDO $pdo = null;

    /** @var array<int, array{sql:string, bindings:array, time:float}> */
    private array $queryLog = [];

    private bool $logEnabled = false;

    public function __construct(private readonly Config $config)
    {
        $this->logEnabled = (bool) $config->get('database.log_queries', false);
    }

    // =========================================================
    // Connection
    // =========================================================

    /**
     * Returns the PDO instance, connecting lazily on first call.
     */
    public function getPdo(): PDO
    {
        if ($this->pdo === null) {
            $this->connect();
        }

        return $this->pdo;
    }

    /**
     * Establishes the database connection.
     *
     * @throws RuntimeException On connection failure
     */
    private function connect(): void
    {
        $conn = $this->config->all('database')['connections']['mariadb'] ?? [];

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $conn['host'] ?? '127.0.0.1',
            $conn['port'] ?? 3306,
            $conn['database'] ?? '',
            $conn['charset'] ?? 'utf8mb4'
        );

        try {
            $this->pdo = new PDO(
                $dsn,
                $conn['username'] ?? 'root',
                $conn['password'] ?? '',
                $conn['options'] ?? [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );

            // Set collation
            $collation = $conn['collation'] ?? 'utf8mb4_unicode_ci';
            $this->pdo->exec("SET NAMES utf8mb4 COLLATE {$collation}");
        } catch (PDOException $e) {
            throw new RuntimeException(
                'Database connection failed: ' . $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }
    }

    // =========================================================
    // Query execution
    // =========================================================

    /**
     * Executes a SELECT query and returns all rows as associative arrays.
     *
     * @param array<int|string, mixed> $bindings
     * @return array<int, array<string, mixed>>
     */
    public function select(string $sql, array $bindings = []): array
    {
        $stmt = $this->executeStatement($sql, $bindings);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Executes a SELECT query and returns the first row or null.
     *
     * @param array<int|string, mixed> $bindings
     * @return array<string, mixed>|null
     */
    public function selectOne(string $sql, array $bindings = []): ?array
    {
        $stmt = $this->executeStatement($sql, $bindings);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    /**
     * Executes an INSERT statement. Returns the last inserted ID.
     *
     * @param array<int|string, mixed> $bindings
     */
    public function insert(string $sql, array $bindings = []): int
    {
        $this->executeStatement($sql, $bindings);
        return (int) $this->getPdo()->lastInsertId();
    }

    /**
     * Executes an UPDATE or DELETE statement. Returns affected row count.
     *
     * @param array<int|string, mixed> $bindings
     */
    public function statement(string $sql, array $bindings = []): int
    {
        $stmt = $this->executeStatement($sql, $bindings);
        return $stmt->rowCount();
    }

    /**
     * Executes a raw SQL string (DDL etc.) — no bindings.
     * Use only for migrations and trusted internal calls.
     */
    public function raw(string $sql): bool
    {
        return $this->getPdo()->exec($sql) !== false;
    }

    // =========================================================
    // Transactions
    // =========================================================

    /**
     * Runs a callback inside a database transaction.
     * Commits on success, rolls back on exception.
     *
     * @template T
     * @param callable(): T $callback
     * @return T
     */
    public function transaction(callable $callback): mixed
    {
        $pdo = $this->getPdo();
        $pdo->beginTransaction();

        try {
            $result = $callback();
            $pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function beginTransaction(): void
    {
        $this->getPdo()->beginTransaction();
    }

    public function commit(): void
    {
        $this->getPdo()->commit();
    }

    public function rollBack(): void
    {
        $this->getPdo()->rollBack();
    }

    // =========================================================
    // Helpers
    // =========================================================

    /**
     * Returns the table prefix configured for this connection.
     */
    public function prefix(): string
    {
        return $this->config->get('database.connections.mariadb.prefix', 'hcms_');
    }

    /**
     * Returns all logged queries (only when debug mode is active).
     *
     * @return array<int, array{sql:string, bindings:array, time:float}>
     */
    public function getQueryLog(): array
    {
        return $this->queryLog;
    }

    // =========================================================
    // Private
    // =========================================================

    /**
     * Prepares and executes a statement, binding parameters safely.
     *
     * @param array<int|string, mixed> $bindings
     */
    private function executeStatement(string $sql, array $bindings): PDOStatement
    {
        $start = microtime(true);

        try {
            $stmt = $this->getPdo()->prepare($sql);
            $stmt->execute($bindings);
        } catch (PDOException $e) {
            throw new RuntimeException(
                "Query failed: {$e->getMessage()} | SQL: {$sql}",
                (int) $e->getCode(),
                $e
            );
        }

        if ($this->logEnabled) {
            $this->queryLog[] = [
                'sql'      => $sql,
                'bindings' => $bindings,
                'time'     => round((microtime(true) - $start) * 1000, 2),
            ];
        }

        return $stmt;
    }
}
