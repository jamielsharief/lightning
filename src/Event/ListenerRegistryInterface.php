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

use Psr\EventDispatcher\ListenerProviderInterface;

/**
 * ListenerRegistryInterface
 *
 * @internal deal with the problem that the PSR does include a standard way
 * to register listeners.
 */
interface ListenerRegistryInterface extends ListenerProviderInterface
{
    /**
     * Registers a Listener for an event type
     */
    public function registerListener(string $eventType, callable $callable): static;

    /**
     * Unregisters a Listener for an event type
     */
    public function unregisterListener(string $eventType, callable $callable): static;
}
