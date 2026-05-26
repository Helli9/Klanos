<?php
namespace App\Core;
//How Auto-Resolution Works (The Logic)   start
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
    public function get(string $id)
    {
        // Already instantiated singleton
        if (isset($this->instances[$id])) {
            return $this->instances[$id];////////////////////////
        }

        // Manual binding exists
        if (isset($this->bindings[$id])) {
            $this->instances[$id] = $this->bindings[$id]($this);

            return $this->instances[$id];///////////////////////
        }

        // Try auto-resolution
        $instance = $this->resolve($id);

        // Cache it
        $this->instances[$id] = $instance;
        return $instance;
    }

    protected function resolve($id) {
        $reflector = new \ReflectionClass($id);

        // Can we even instantiate this? (Check for abstract classes/interfaces)
        if (!$reflector->isInstantiable()) {
            throw new \RuntimeException("Class {$id} is not instantiable.");
        }

        $constructor = $reflector->getConstructor();

        // If no constructor, just new up the object
        if (is_null($constructor)) {
            return new $id;
        }

        $parameters = $constructor->getParameters();
        $dependencies = [];

        // Loop through constructor arguments
        foreach ($parameters as $parameter) {//////////////////////////////////
            $type = $parameter->getType();

            if (!$type || $type->isBuiltin()) {
                // It's a string, int, etc. We don't know what to inject unless there's a default value.
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                    continue;
                }
                throw new \RuntimeException("Cannot resolve built-in parameter {$parameter->getName()}");
            }

            // It's a class! Recursively resolve it through the container
            $dependencies[] = $this->get($type->getName());
        }

        // Instantiate the class with the resolved dependencies
        return $reflector->newInstanceArgs($dependencies);
    }
}