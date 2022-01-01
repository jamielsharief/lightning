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

use Lightning\Entity\EntityInterface;
use Lightning\DataMapper\AbstractDataMapper;
use Psr\EventDispatcher\StoppableEventInterface;

abstract class AbstractBeforeWriteEvent implements StoppableEventInterface
{
    protected AbstractDataMapper $dataMapper;
    protected EntityInterface $entity;

    protected bool $stopped = false;

    public function __construct(AbstractDataMapper $dataMapper, EntityInterface $entity)
    {
        $this->dataMapper = $dataMapper;
        $this->entity = $entity;
    }

    /**
     * Gets the DataMapper
     *
     * @return  AbstractDataMapper
     */
    public function getDataMapper(): AbstractDataMapper
    {
        return $this->dataMapper;
    }

    /**
     * Gets the Entity for this Event
     *
     * @return Entity
     */
    public function getEntity(): EntityInterface
    {
        return $this->entity;
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
