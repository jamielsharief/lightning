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

    public function getQuery(): QueryObject
    {
        return $this->query;
    }

    public function stop(): self
    {
        $this->stopped = true;

        return $this;
    }

    public function isPropagationStopped(): bool
    {
        return $this->stopped;
    }
}
