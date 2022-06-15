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

namespace Lightning\Router\Event;

use Psr\EventDispatcher\StoppableEventInterface;

class BeforeFilterEvent extends AbstractFilterEvent implements StoppableEventInterface
{
    protected bool $stopped = false;

    /**
     * Stops the Event
     */
    public function stop(): static
    {
        $this->stopped = true;

        return $this;
    }

    /**
     * Checks if the Event was stopped
     */
    public function isPropagationStopped(): bool
    {
        return $this->stopped;
    }
}
