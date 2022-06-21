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

use RuntimeException;
use InvalidArgumentException;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * Event - A generic Event
 *
 * @internal Event Type not Event name is the correct naming strategy
 */
class Event implements StoppableEventInterface
{
    /**
     * Event type e.g. Order.placed
     */
    protected string $type;

    /**
     * Status
     */
    private bool $stopped = false;

    /**
     * Event can be cancelled
     */
    private bool $cancelable = true;

    /**
     * Event subject (source where this event was created)
     */
    protected ?object $source;

    /**
     * Event data
     */
    protected array $data = [];

    /**
     * Constructor
     */
    public function __construct(string $type, ?object $source = null, array $data = [], bool $cancelable = true)
    {
        $this->type = $type;
        $this->source = $source;
        $this->data = $data;
        $this->cancelable = $cancelable;
    }

    /**
     * Gets the event type, e.g. Order.placed
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Gets the source (subject) that triggered this Event
     */
    public function getSource(): ?object
    {
        return $this->source;
    }

    /**
     * Stops further propagation of the current Event
     */
    public function stop(): static
    {
        if ($this->cancelable === false) {
            throw new RuntimeException('This event cannot be stopped');
        }
        $this->stopped = true;

        return $this;
    }

    /**
     * Checks if the Event has been stopped
     */
    public function isPropagationStopped(): bool
    {
        return $this->stopped === true;
    }

    /**
     * Checks if the event can be cancelled
     */
    public function isCancelable(): bool
    {
        return $this->cancelable === true;
    }

    /**
     * Sets data for this Event
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get the Event data
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Sets a value in the event object
     */
    public function set(string $name, mixed $value): static
    {
        $this->data[$name] = $value;

        return $this;
    }

    /**
     * Gets a value from the Event
     */
    public function get(string $name): mixed
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        throw new InvalidArgumentException(sprintf('`%s` not found', $name));
    }

    /**
     * Checks if there is a value in the Event
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->data);
    }
}
