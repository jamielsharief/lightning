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

namespace Lightning\Controller\Event;

use Psr\EventDispatcher\StoppableEventInterface;

abstract class AbstractControllerStoppableEvent extends AbstractControllerEvent implements StoppableEventInterface
{
    protected bool $stopped = false;

    /**
     * Stops the Event
     *
     * @return self
     */
    public function stop(): self
    {
        $this->stopped = true;

        return $this;
    }

    /**
     * Checks if the Event was stopped
     *
     * @return boolean
     */
    public function isPropagationStopped(): bool
    {
        return $this->stopped;
    }
}
