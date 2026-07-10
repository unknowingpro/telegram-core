<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

/**
 * PDO singleton with fluent query builder
 * Returns clones on every chain call to prevent state bleeding
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;
    private string $table = '';
    private array $wheres = [];
    private array $orderBy = [];
    private ?int $limitVal = null;
    private ?int $offsetVal = null;
    private array $bindings = [];

    private function __construct(array $config)
    {
        $dsn = sprintf(
            '%s:host=%s;port=%d;dbname=%s;charset=%s',
            $config['driver'],
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        $this->pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    public static function getInstance(?array $config = null): self
    {
        if (self::$instance === null) {
            if ($config === null) {
                throw new \RuntimeException('Database config required on first call');
            }
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * Start a query on a table
     */
    public function table(string $table): self
    {
        $clone = clone $this;
        $clone->table = $table;
        $clone->wheres = [];
        $clone->orderBy = [];
        $clone->limitVal = null;
        $clone->offsetVal = null;
        $clone->bindings = [];
        return $clone;
    }

    /**
     * Add a WHERE condition
     */
    public function where(string $column, mixed $operator, mixed $value = null): self
    {
        $clone = clone $this;

        if ($value === null) {
            // where('col', value) — implicit equals
            $value = $operator;
            $operator = '=';
        }

        $paramKey = ':w' . count($clone->bindings);
        $clone->wheres[] = "{$column} {$operator} {$paramKey}";
        $clone->bindings[$paramKey] = $value;

        return $clone;
    }

    /**
     * Add an IN clause
     */
    public function whereIn(string $column, array $values): self
    {
        $clone = clone $this;
        $params = [];
        foreach ($values as $i => $value) {
            $paramKey = ':win' . count($clone->bindings);
            $params[] = $paramKey;
            $clone->bindings[$paramKey] = $value;
        }
        $clone->wheres[] = "{$column} IN (" . implode(',', $params) . ")";
        return $clone;
    }

    /**
     * Add ORDER BY
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $clone = clone $this;
        $clone->orderBy[] = "{$column} {$direction}";
        return $clone;
    }

    /**
     * Set LIMIT
     */
    public function limit(int $limit): self
    {
        $clone = clone $this;
        $clone->limitVal = $limit;
        return $clone;
    }

    /**
     * Set OFFSET
     */
    public function offset(int $offset): self
    {
        $clone = clone $this;
        $clone->offsetVal = $offset;
        return $clone;
    }

    /**
     * Execute SELECT and return all rows
     */
    public function get(): array
    {
        $sql = $this->buildSelect();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->bindings);
        return $stmt->fetchAll();
    }

    /**
     * Execute SELECT and return first row
     */
    public function first(): ?array
    {
        $clone = $this->limit(1);
        $rows = $clone->get();
        return $rows[0] ?? null;
    }

    /**
     * Count rows
     */
    public function count(): int
    {
        $clone = clone $this;
        $sql = "SELECT COUNT(*) as cnt FROM {$clone->table}";
        if ($clone->wheres) {
            $sql .= " WHERE " . implode(' AND ', $clone->wheres);
        }
        $stmt = $clone->pdo->prepare($sql);
        $stmt->execute($clone->bindings);
        return (int) $stmt->fetch()['cnt'];
    }

    /**
     * Insert a row
     */
    public function insert(array $data): string
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($data));

        return $this->pdo->lastInsertId();
    }

    /**
     * Update rows
     */
    public function update(array $data): int
    {
        $setClauses = [];
        $values = [];
        foreach ($data as $column => $value) {
            $setClauses[] = "{$column} = ?";
            $values[] = $value;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClauses);
        if ($this->wheres) {
            $sql .= " WHERE " . implode(' AND ', $this->wheres);
            $values = array_merge($values, array_values($this->bindings));
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($values);
        return $stmt->rowCount();
    }

    /**
     * Delete rows
     */
    public function delete(): int
    {
        $sql = "DELETE FROM {$this->table}";
        if ($this->wheres) {
            $sql .= " WHERE " . implode(' AND ', $this->wheres);
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->bindings);
        return $stmt->rowCount();
    }

    /**
     * Raw query with bindings
     */
    public function raw(string $sql, array $bindings = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        return $stmt;
    }

    /**
     * Fetch all from raw query
     */
    public function rawFetchAll(string $sql, array $bindings = []): array
    {
        return $this->raw($sql, $bindings)->fetchAll();
    }

    /**
     * Fetch one from raw query
     */
    public function rawFetch(string $sql, array $bindings = []): ?array
    {
        return $this->raw($sql, $bindings)->fetch() ?: null;
    }

    /**
     * Check if a table exists
     */
    public function tableExists(string $table): bool
    {
        $result = $this->rawFetch(
            "SELECT COUNT(*) as cnt FROM information_schema.tables WHERE table_name = ?",
            [$table]
        );
        return ($result['cnt'] ?? 0) > 0;
    }

    /**
     * Begin a transaction
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    public function rollback(): bool
    {
        return $this->pdo->rollBack();
    }

    private function buildSelect(): string
    {
        $sql = "SELECT * FROM {$this->table}";
        if ($this->wheres) {
            $sql .= " WHERE " . implode(' AND ', $this->wheres);
        }
        if ($this->orderBy) {
            $sql .= " ORDER BY " . implode(', ', $this->orderBy);
        }
        if ($this->limitVal !== null) {
            $sql .= " LIMIT {$this->limitVal}";
        }
        if ($this->offsetVal !== null) {
            $sql .= " OFFSET {$this->offsetVal}";
        }
        return $sql;
    }
}
