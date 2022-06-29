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

namespace Lightning\Event;

/**
 * AbstractListener
 *
 * Goals: have own listener class without having to call PHP magic method, typehinting in main method and works with other dispatchers.
 * Can't overload interfaces or methods with the correct object declaration therefore a kind of adapter.
 */
abstract class AbstractListener
{
    /**
     * Invoke the listener
     */
    public function __invoke(object $event): void
    {
        call_user_func([$this, 'handle'], $event);
    }
}
