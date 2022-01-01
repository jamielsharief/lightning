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

use ArrayObject;
use Lightning\DataMapper\ResultSet;
use Lightning\DataMapper\QueryObject;
use Lightning\DataMapper\AbstractDataMapper;

class AfterFindEvent
{
    protected ResultSet $resultSet;
    protected QueryObject $query;
    protected AbstractDataMapper $dataMapper;

    /**
     * Constructor
     *
     * @param AbstractDataMapper $dataMapper
     * @param Collection $resultSet
     * @param ArrayObject $options
     */
    public function __construct(AbstractDataMapper $dataMapper, ResultSet $resultSet, QueryObject $query)
    {
        $this->dataMapper = $dataMapper;
        $this->resultSet = $resultSet;
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

    /**
     * Get the colleciton of the results
     *
     * @return ResultSet
     */
    public function getResultSet(): ResultSet
    {
        return $this->resultSet;
    }
}
