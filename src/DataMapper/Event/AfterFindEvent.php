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

use Lightning\Utility\Collection;
use Lightning\DataMapper\QueryObject;
use Lightning\DataMapper\AbstractDataMapper;

class AfterFindEvent
{
    protected Collection $collection;
    protected QueryObject $query;
    protected AbstractDataMapper $dataMapper;

    /**
     * Constructor
     */
    public function __construct(AbstractDataMapper $dataMapper, Collection $collection, QueryObject $query)
    {
        $this->dataMapper = $dataMapper;
        $this->collection = $collection;
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
     */
    public function getCollection(): Collection
    {
        return $this->collection;
    }
}
