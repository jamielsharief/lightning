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

namespace Lightning\Hook;

use InvalidArgumentException;

/**
 * Hook Trait
 *
 * Goals: register and unregister hooks programatically on object
 */
trait HookTrait
{
    /**
     * Registered hooks
     */
    protected array $registeredHooks = [];

    /**
     * Registers a hook
     */
    public function registerHook(string $name, string $method): static
    {
        if (! method_exists($this, $method)) {
            throw new InvalidArgumentException(sprintf('Hook method `%s` does not exist', $method)); // This is not fine
        }

        if (! isset($this->registeredHooks[$name])) {
            $this->registeredHooks[$name] = [];
        }

        $this->registeredHooks[$name][] = $method;

        return $this;
    }

    /**
     * Unregisters a hook that was registered
     */
    public function unregisterHook(string $name, string $method): static
    {
        foreach ($this->registeredHooks[$name] ?? [] as $index => $objectMethod) {
            if ($method === $objectMethod) {
                unset($this->registeredHooks[$name][$index]);
            }
        }

        return $this;
    }

    public function hasRegisteredHook(string $name, string $method): bool
    {
        $registeredHooks = $this->registeredHooks[$name] ?? [];

        return in_array($method, $registeredHooks);
    }

    /**
     * Trigger a hook
     */
    public function triggerHook(string $name, array $arguments = [], bool $isStoppable = true): bool
    {
        foreach ($this->registeredHooks[$name] ?? [] as $method) {
            if ($this->$method(...$arguments) === false && $isStoppable) {
                return false;
            }
        }

        return true;
    }
}
