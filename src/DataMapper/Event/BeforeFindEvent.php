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

namespace Lightning\DataMapper\Event;

use Lightning\DataMapper\QueryObject;
use Lightning\DataMapper\AbstractDataMapper;
use Psr\EventDispatcher\StoppableEventInterface;

class BeforeFindEvent implements StoppableEventInterface
{
    protected AbstractDataMapper $dataMapper;
    protected QueryObject $query;

    protected bool $stopped = false;

    /**
     * Constructor
     */
    public function __construct(AbstractDataMapper $dataMapper, QueryObject $query)
    {
        $this->dataMapper = $dataMapper;
        $this->query = $query;
    }

    /**
     * Gets the DataMapper
     */
    public function getDataMapper(): AbstractDataMapper
    {
        return $this->dataMapper;
    }

    /**
     * Gets the Query Object
     */
    public function getQuery(): QueryObject
    {
        return $this->query;
    }

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
