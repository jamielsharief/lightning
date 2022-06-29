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

use Psr\EventDispatcher\StoppableEventInterface;

/**
 * AbstractStoppableEvent
 */
abstract class AbstractStoppableEvent extends AbstractEvent implements StoppableEventInterface
{
    private bool $propagationStopped = false;

    /**
     * Is propagation stopped?
     */
    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    /**
     * Stop the event propagation
     */
    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }
}
