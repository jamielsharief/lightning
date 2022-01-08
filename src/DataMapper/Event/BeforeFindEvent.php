<?php declare(strict_types=1);
/**
 * LightningPHP
 * Copyright 2021 Jamiel Sharief.
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
     *
     * @param AbstractDataMapper $dataMapper
     * @param QueryObject $query
     */
    public function __construct(AbstractDataMapper $dataMapper, QueryObject $query)
    {
        $this->dataMapper = $dataMapper;
        $this->query = $query;
    }

    /**
     * Gets the DataMapper
     *
     * @return AbstractDataMapper
     */
    public function getDataMapper(): AbstractDataMapper
    {
        return $this->dataMapper;
    }

    /**
     * Gets the Query Object
     *
     * @return QueryObject
     */
    public function getQuery(): QueryObject
    {
        return $this->query;
    }

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
