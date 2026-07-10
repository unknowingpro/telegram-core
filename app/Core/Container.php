<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Static DI container
 * Simple bind/make pattern for dependency injection
 */
class Container
{
    private array $bindings = [];
    private array $instances = [];

    /**
     * Bind a value or factory to a key
     */
    public function bind(string $key, mixed $value): void
    {
        $this->bindings[$key] = $value;
    }

    /**
     * Bind a singleton (created once, reused)
     */
    public function singleton(string $key, callable $factory): void
    {
        $this->bindings[$key] = $factory;
        $this->instances[$key] = null;
    }

    /**
     * Resolve a binding
     */
    public function make(string $key): mixed
    {
        // Return cached instance if singleton
        if (array_key_exists($key, $this->instances)) {
            return $this->instances[$key];
        }

        if (!isset($this->bindings[$key])) {
            throw new \RuntimeException("No binding found for: {$key}");
        }

        $value = $this->bindings[$key];

        if (is_callable($value)) {
            $value = $value($this);
        }

        // Cache singletons
        if (array_key_exists($key, $this->instances)) {
            $this->instances[$key] = $value;
        }

        return $value;
    }

    /**
     * Check if a binding exists
     */
    public function has(string $key): bool
    {
        return isset($this->bindings[$key]);
    }

    /**
     * Remove a binding
     */
    public function forget(string $key): void
    {
        unset($this->bindings[$key], $this->instances[$key]);
    }
}
