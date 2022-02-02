<?php declare(strict_types=1);
/**
 * LightningPHP
 * Copyright 2021 - 2022 Jamiel Sharief.
 *
 * Licensed under The MIT License
 * The above copyright notice and this permission notice shall be included in all copies or substantial
 * portions of the Software.
 *
 * @copyright   Copyright (c) Jamiel Sharief
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */

namespace Lightning\Autowire;

use Closure;
use ReflectionClass;
use ReflectionFunction;
use ReflectionParameter;
use Psr\Container\ContainerInterface;
use Lightning\Autowire\Exception\AutowireException;

/**
 * Autowire - Automatically create and inject dependencies compatible with PSR-11
 */
class Autowire
{
    protected ?ContainerInterface $container;

    /**
     * Constructor
     *
     * @param ContainerInterface|null $container
     */
    public function __construct(?ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Sets the PSR-11 Container to be used
     *
     * @param ContainerInterface $container
     * @return self
     */
    public function setContainer(ContainerInterface $container): self
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Autowires a class using the constructor method
     *
     * @internal I put parameters here as well to keep consistent despite being unsure.
     *
     * @param string $class
     * @param array $parameters
     * @return object
     */
    public function class(string $class, array $parameters = []): object
    {
        if (! class_exists($class)) {
            throw new AutowireException(sprintf('`%s` could not be found"', $class));
        }
        $reflection = new ReflectionClass($class);

        if (! $reflection->isInstantiable()) {
            throw new AutowireException(sprintf('Class `%s` is not instantiable', $reflection->getName()));
        }

        $reflectionMethod = $reflection->getConstructor();

        return $reflection->newInstanceArgs(
            $this->resolveParameters($reflectionMethod ? $reflectionMethod->getParameters() : [], $parameters)
        );
    }

    /**
     * Invokes a method on the object
     *
     * @param object $object
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function method(object $object, string $method, array $parameters = [])
    {
        if (! method_exists($object, $method)) {
            throw new AutowireException(sprintf('`%s` does not have the `%s` method', get_class($object), $method));
        }

        $reflection = (new ReflectionClass($object))->getMethod($method);

        $vars = $this->resolveParameters($reflection ->getParameters(), $parameters);

        return call_user_func_array([$object,$method], $vars);
    }

    /**
     * Undocumented function
     *
     * @param string|closure $function
     * @return mixed
     */
    public function function($function, array $parameters = [])
    {
        $reflectionFunction = new ReflectionFunction($function);

        return call_user_func_array(
            $function, $this->resolveParameters($reflectionFunction->getParameters(), $parameters)
        );
    }

    /**
     * Resolves an array of paramaters
     *
     * @param array $parameters
     * @return array
    */
    protected function resolveParameters(array $parameters, array $vars = []): array
    {
        return array_map(function ($parameter) use ($vars) {
            return $this->resolveParameter($parameter, $vars);
        }, $parameters);
    }

    /**
     * Resolves a paramater
     *
     * @param ReflectionParameter $parameter
     * @return mixed
     */
    protected function resolveParameter(ReflectionParameter $parameter, array $vars = [])
    {

        /** @var \ReflectionNamedType $parameterType */
        $parameterType = $parameter->getType();

        if (! $parameterType) {
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }

            throw new AutowireException(sprintf('constructor parameter `%s` has no type or default value', $parameter->name));
        }

        $hasDefaultValue = $parameter->isDefaultValueAvailable();

        if ($parameterType->isBuiltin()) {
            if (isset($vars[$parameter->name])) {
                return $vars[$parameter->name];
            }
            if ($hasDefaultValue) {
                return $parameter->getDefaultValue();
            }

            throw new AutowireException(sprintf('parameter `%s` has no default value', $parameter->name));
        }

        $service = $parameterType->getName();

        if ($this->container && $this->container->has($service)) {
            return $this->container->get($service);
        } elseif (class_exists($service)) {
            return $this->class($service);
        }

        if (isset($vars[$service])) {
            return $vars[$service];
        }

        if ($hasDefaultValue) {
            return $parameter->getDefaultValue();
        }

        throw new AutowireException(sprintf('Class `%s` not found', $service));
    }
}
