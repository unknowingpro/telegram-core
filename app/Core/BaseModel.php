<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Base model with common CRUD helpers
 * All models extend this
 */
abstract class BaseModel
{
    protected Database $db;
    protected string $table = '';
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Find by primary key
     */
    public function find(int|string $id): ?array
    {
        return $this->db->table($this->table)
            ->where($this->primaryKey, $id)
            ->first();
    }

    /**
     * Find by a column value
     */
    public function findBy(string $column, mixed $value): ?array
    {
        return $this->db->table($this->table)
            ->where($column, $value)
            ->first();
    }

    /**
     * Get all rows with optional conditions
     */
    public function all(array $conditions = [], int $limit = 100, int $offset = 0): array
    {
        $query = $this->db->table($this->table);

        foreach ($conditions as $column => $value) {
            $query = $query->where($column, $value);
        }

        return $query->orderBy($this->primaryKey, 'DESC')
            ->limit($limit)
            ->offset($offset)
            ->get();
    }

    /**
     * Count rows with optional conditions
     */
    public function count(array $conditions = []): int
    {
        $query = $this->db->table($this->table);

        foreach ($conditions as $column => $value) {
            $query = $query->where($column, $value);
        }

        return $query->count();
    }

    /**
     * Create a new row
     */
    public function create(array $data): string
    {
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        return $this->db->table($this->table)->insert($data);
    }

    /**
     * Update a row by primary key
     */
    public function update(int|string $id, array $data): int
    {
        return $this->db->table($this->table)
            ->where($this->primaryKey, $id)
            ->update($data);
    }

    /**
     * Delete a row by primary key
     */
    public function delete(int|string $id): int
    {
        return $this->db->table($this->table)
            ->where($this->primaryKey, $id)
            ->delete();
    }

    /**
     * Soft delete (set deleted_at)
     */
    public function softDelete(int|string $id): int
    {
        return $this->update($id, [
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Generate a unique ID (Telegram-style snowflake or simple increment)
     */
    protected function generateId(): int
    {
        // Simple snowflake-like ID: timestamp in seconds * 1000 + random
        return (int) (microtime(true) * 1000) + random_int(0, 999);
    }
}
