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

use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * EventManagerInterface
 *
 * The PSR does not provide this, so if i wanted to programmatically add listeners but swapped out libaries I would run into a trouble. I also think
 * the dispatch method should have been called dispatchEvent, to do some crazy stuff.
 */
interface EventManagerInterface extends EventDispatcherInterface
{
    public function addListener(string $eventName, callable $callable, int $priority = 10): static;

    public function removeListener(string $eventName, callable $callable): static;
}
