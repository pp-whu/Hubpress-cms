<?php

declare(strict_types=1);

namespace HuberCMS\Core;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;

/**
 * Container
 *
 * A lightweight PSR-11-compatible Dependency Injection Container.
 * Supports:
 * - Singleton bindings (shared instances)
 * - Transient bindings (new instance per make())
 * - Direct instance registration
 * - Automatic constructor injection via Reflection
 *
 * @package HuberCMS\Core
 */
final class Container
{
    /** @var array<string, Closure> Transient factory closures */
    private array $bindings = [];

    /** @var array<string, Closure> Singleton factory closures */
    private array $singletons = [];

    /** @var array<string, object> Resolved singleton instances */
    private array $instances = [];

    /**
     * Registers a transient binding — a new instance each time make() is called.
     */
    public function bind(string $abstract, Closure $factory): void
    {
        $this->bindings[$abstract] = $factory;
    }

    /**
     * Registers a singleton binding — the same instance is returned every time.
     */
    public function singleton(string $abstract, Closure $factory): void
    {
        $this->singletons[$abstract] = $factory;
    }

    /**
     * Registers an already-resolved object as a shared instance.
     */
    public function instance(string $abstract, object $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    /**
     * Resolves and returns an instance for the given abstract identifier.
     *
     * Resolution order:
     * 1. Already-resolved singleton instance
     * 2. Singleton factory closure (resolved and cached)
     * 3. Transient factory closure (resolved fresh)
     * 4. Auto-wired via Reflection
     *
     * @template T of object
     * @param class-string<T>|string $abstract
     * @return T
     * @throws ContainerException
     */
    public function make(string $abstract): object
    {
        // 1. Return cached instance (singleton or manually registered)
        if (isset($this->instances[$abstract])) {
            /** @var T */
            return $this->instances[$abstract];
        }

        // 2. Singleton factory — resolve once and cache
        if (isset($this->singletons[$abstract])) {
            $instance = ($this->singletons[$abstract])($this);
            $this->instances[$abstract] = $instance;
            return $instance;
        }

        // 3. Transient factory — resolve fresh each time
        if (isset($this->bindings[$abstract])) {
            return ($this->bindings[$abstract])($this);
        }

        // 4. Auto-wire via Reflection
        return $this->build($abstract);
    }

    /**
     * Checks whether the container has a binding or instance for the given abstract.
     */
    public function has(string $abstract): bool
    {
        return isset($this->instances[$abstract])
            || isset($this->singletons[$abstract])
            || isset($this->bindings[$abstract]);
    }

    /**
     * Removes a binding and its cached instance.
     */
    public function forget(string $abstract): void
    {
        unset($this->bindings[$abstract], $this->singletons[$abstract], $this->instances[$abstract]);
    }

    /**
     * Auto-wires a class by resolving its constructor dependencies recursively.
     *
     * @template T of object
     * @param class-string<T> $concrete
     * @return T
     * @throws ContainerException
     */
    private function build(string $concrete): object
    {
        try {
            $reflection = new ReflectionClass($concrete);
        } catch (ReflectionException $e) {
            throw new ContainerException(
                "Cannot resolve [{$concrete}]: class does not exist.",
                previous: $e
            );
        }

        if (!$reflection->isInstantiable()) {
            throw new ContainerException(
                "Cannot resolve [{$concrete}]: class is not instantiable " .
                "(abstract class, interface, or private constructor)."
            );
        }

        $constructor = $reflection->getConstructor();

        // No constructor — just instantiate
        if ($constructor === null) {
            return new $concrete();
        }

        $parameters = $constructor->getParameters();
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                // Recursively resolve typed dependencies
                $dependencies[] = $this->make($type->getName());
            } elseif ($parameter->isDefaultValueAvailable()) {
                // Use default value for primitives
                $dependencies[] = $parameter->getDefaultValue();
            } else {
                throw new ContainerException(
                    "Cannot resolve parameter [{$parameter->getName()}] " .
                    "in class [{$concrete}]: no type hint and no default value."
                );
            }
        }

        return $reflection->newInstanceArgs($dependencies);
    }
}
