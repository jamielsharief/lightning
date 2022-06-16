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

use Lightning\Entity\EntityInterface;
use Lightning\DataMapper\AbstractDataMapper;

abstract class AbstractAfterWriteEvent
{
    protected AbstractDataMapper $dataMapper;
    protected EntityInterface $entity;

    public function __construct(AbstractDataMapper $dataMapper, EntityInterface $entity)
    {
        $this->dataMapper = $dataMapper;
        $this->entity = $entity;
    }

    /**
     * Gets the DataMapper
     */
    public function getDataMapper(): AbstractDataMapper
    {
        return $this->dataMapper;
    }

    /**
     * Gets the Entity for this Event
     */
    public function getEntity(): EntityInterface
    {
        return $this->entity;
    }
}
