<?php

namespace Tests\Unit\Core;

use App\Core\Container;
use PHPUnit\Framework\TestCase;
use RuntimeException;

// --- Test Helper Classes ---

class DependencyNoConstructor {}

class DependencyWithPrimitive {
    public function __construct(public string $name = 'default_val') {}
}

class DependencyUnresolvable {
    public function __construct(public int $id) {} // No default value
}

class DependentClass {
    public function __construct(public DependencyNoConstructor $dep) {}
}

// Used to safely trigger the non-instantiable check
abstract class AbstractDependency {}

// --- Test Suite ---

class ContainerTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new Container();
    }

    public function test_get_returns_cached_singleton_instance(): void
    {
        $instance1 = $this->container->get(DependencyNoConstructor::class);
        $instance2 = $this->container->get(DependencyNoConstructor::class);

        $this->assertSame($instance1, $instance2);
    }

    public function test_get_resolves_manual_binding(): void
    {
        $this->container->set(DependencyNoConstructor::class, function () {
            return new DependencyNoConstructor();
        });

        $instance = $this->container->get(DependencyNoConstructor::class);
        $this->assertInstanceOf(DependencyNoConstructor::class, $instance);
    }

    public function test_get_auto_resolves_class_without_constructor(): void
    {
        $instance = $this->container->get(DependencyNoConstructor::class);
        $this->assertInstanceOf(DependencyNoConstructor::class, $instance);
    }

    public function test_get_auto_resolves_class_with_dependencies(): void
    {
        $instance = $this->container->get(DependentClass::class);
        
        $this->assertInstanceOf(DependentClass::class, $instance);
        $this->assertInstanceOf(DependencyNoConstructor::class, $instance->dep);
    }

    public function test_get_resolves_primitive_with_default_value(): void
    {
        $instance = $this->container->get(DependencyWithPrimitive::class);
        $this->assertSame('default_val', $instance->name);
    }

    public function test_get_throws_exception_if_class_not_instantiable(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('is not instantiable');

        // Abstract classes exist but cannot be instantiated, hitting your check
        $this->container->get(AbstractDependency::class);
    }

    public function test_get_throws_exception_for_unresolvable_primitive(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot resolve built-in parameter id');

        $this->container->get(DependencyUnresolvable::class);
    }
}