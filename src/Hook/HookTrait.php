<?php declare(strict_types=1);
/**
 * LightningPHP
 * Copyright 2021 Jamiel Sharief.
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

trait HookTrait
{
    protected $registeredHooks = [];

    /**
     * Registers a hook
     *
     * @param string $name
     * @param string $method
     * @return void
     */
    public function registerHook(string $name, string $method): void
    {
        if (! method_exists($this, $method)) {
            throw new InvalidArgumentException(sprintf('Hook method `%s` does not exist', $method)); // This is not fine
        }

        $this->registeredHooks[$name][] = $method;
    }

    /**
     * Undocumented function
     *
     * @param string $name
     * @param array $arguments
     * @param boolean $isStoppable
     * @return boolean
     */
    public function triggerHook(string $name, array $arguments = [], bool $isStoppable = true): bool
    {
        $hooks = $this->registeredHooks[$name] ?? [];

        if ($hooks) {
            foreach ($hooks as $method) {
                if (call_user_func_array([$this,$method], $arguments) === false && $isStoppable) {
                    return false;
                }
            }
        }

        return true;
    }
}
