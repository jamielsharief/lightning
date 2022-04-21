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

use Lightning\Event\Exception\EventException;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * Event - A generic Event
 */
class Event implements GenericEventInterface, StoppableEventInterface
{
    /**
     * Event name e.g. Order.placed
     *
     * @var string
     */
    protected string $name;

    /**
     * Status
     *
     * @var boolean
     */
    private bool $stopped = false;

    /**
     * Event can be cancelled
     *
     * @var boolean
     */
    private bool $cancelable = true;

    /**
     * Event subject (source where this event was created)
     *
     * @var object|null
     */
    protected ?object $source;

    /**
     * Event data
     *
     * @var array
     */
    protected array $data = [];

    /**
     * Constructor
     *
     * @param string $name Type of event e.g Order.placed
     * @param object $source The object that triggered the event
     * @param array $data   Extra data passed to the event
     * @param boolean $cancelable If the event is cancelable
     */
    public function __construct(string $name, ?object $source = null, array $data = [], bool $cancelable = true)
    {
        $this->name = $name;
        $this->source = $source;
        $this->data = $data;
        $this->cancelable = $cancelable;
    }

    /**
     * Gets the event name, e.g. Order.placed
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Gets the source (subject) that triggered this Event
     *
     * @return object|null
     */
    public function getSource(): ?object
    {
        return $this->source;
    }

    /**
     * Stops further propagation of the current Event
     *
     * @return self
     */
    public function stop(): self
    {
        if ($this->cancelable === false) {
            throw new EventException('This event cannot be stopped');
        }
        $this->stopped = true;

        return $this;
    }

    /**
     * Checks if the Event has been stopped
     *
     * @return boolean
     */
    public function isPropagationStopped(): bool
    {
        return $this->stopped === true;
    }

    /**
     * Checks if the event can be cancelled
     *
     * @return boolean
     */
    public function isCancelable(): bool
    {
        return $this->cancelable === true;
    }

    /**
     * Sets data for this Event
     *
     * @param array $data
     * @return self
     */
    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get the Event data
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Returns a new instance with this data
     *
     * @param array $data
     * @return static
     */
    public function withData(array $data): self
    {
        return (clone $this)->setData($data);
    }
}
