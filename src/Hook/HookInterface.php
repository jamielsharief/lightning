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

interface HookInterface
{
    /**
     * Trigger hook
     *
     * @param string $name
     * @param array $arguments
     * @param boolean $isStoppable
     * @return boolean
     */
    public function triggerHook(string $name, array $arguments = [], bool $isStoppable = true): bool;
}
