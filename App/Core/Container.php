<?php
namespace App\Core;

class Container
{
    private array $bindings = [];
    private array $instances = [];

    // set() - type less by returning static for chaining
    public function set(string $id, callable $closure): static {
        $this->bindings[$id] = $closure;
        unset($this->instances[$id]); // Clear cache if overwritten
        return $this;
    }

    // get() - lazy instantiates and caches the object
    public function get(string $id) {
        if (!isset($this->instances[$id])) {
            if (!isset($this->bindings[$id])) {
                throw new \RuntimeException("Identifier '{$id}' is not bound.");
            }
            // Execute the closure and cache the result
            $this->instances[$id] = $this->bindings[$id]($this);
        }
        return $this->instances[$id];
    }
}