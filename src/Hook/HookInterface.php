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

/**
 * An interface to help identify objects that are hook ready
 */
interface HookInterface
{
    /**
     * Registers a hook on the object
     */
    public function registerHook(string $name, string $method): static;

    /**
     * Triggers a hook on the object
     */
    public function triggerHook(string $name, array $arguments = [], bool $isStoppable = true): bool;
}
