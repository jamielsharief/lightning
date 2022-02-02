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

namespace Lightning\DataMapper\DataSource;

use RuntimeException;
use Lightning\Criteria\Criteria;
use Lightning\DataMapper\ResultSet;
use Lightning\DataMapper\QueryObject;
use Lightning\DataMapper\DataSourceInterface;

/**
 * Memory Data Source
 *
 * @internal Data sources have different features
 */
class MemoryDataSource implements DataSourceInterface
{
    protected array $data;
    private ?int $lastInsertId = null;
    private int $autoIncrement;

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct(array $data = [], int $autoIncrement = 0)
    {
        $this->data = $data;
        $this->autoIncrement = $autoIncrement;
    }

    public function getGeneratedId()
    {
        return $this->lastInsertId;
    }

    /**
     * Creates a record in the datasource
     *
     * @param string $collection
     * @param array $data
     * @return boolean
     */
    public function create(string $collection, array $data): bool
    {
        if (! isset($this->data[$collection])) {
            $this->data[$collection] = [];
        }

        $this->autoIncrement++;
        while (isset($this->data[$collection][$this->autoIncrement])) {
            $this->autoIncrement++;
        }

        $id = $this->lastInsertId = $this->autoIncrement;

        $this->data[$collection][$id] = $data;

        return true;
    }

    /**
     * @param string $collection
     * @param QueryObject $query
     * @param boolean $preserveKeys
     * @return ResultSet
     */
    public function read(string $collection, QueryObject $query, bool $preserveKeys = false): ResultSet
    {
        $criteria = $query->getCriteria();
        $options = $query->getOptions();

        $limit = $options['limit'] ?? null;
        $offset = $options['offset'] ?? 0;

        $data = $this->data[$collection] ?? [];

        if (! empty($options['order'])) {
            $data = $this->sortData($data, $options['order']);
        }

        $criteria = $this->createCriteria($criteria);

        $result = [];
        $i = 0;
        $found = 0;
        foreach ($data as $id => $row) {
            if ($i >= $offset && $criteria->match($row)) {
                if ($preserveKeys) {
                    $result[$id] = $row;
                } else {
                    $result[] = $row;
                }

                $found++;
            }
            if ($limit && $found === $limit) {
                break;
            }
            $i++;
        }

        return new ResultSet($result);
    }

    /**
     * Update
     *
     * @param string $collection
     * @param QueryObject $query
     * @param array $data
     * @return integer
     */
    public function update(string $collection, QueryObject $query, array $data): int
    {
        $updated = 0;
        foreach ($this->read($collection, $query, true) as $id => $row) {
            $this->data[$collection][$id] = array_merge($this->data[$collection][$id], $data);
            $updated ++;
        }

        return $updated;
    }

    /**
     * Deletes from the Datasource
     *
     * @param string $collection
     * @param QueryObject $query
     * @return integer
     */
    public function delete(string $collection, QueryObject $query): int
    {
        $deleted = 0;
        foreach ($this->read($collection, $query, true) as  $id => $row) {
            unset($this->data[$collection][$id]);
            $deleted ++;
        }

        return $deleted;
    }

    /**
     * Counts the number of records in the data source
     *
     * @param string $collection
     * @param QueryObject $query
     * @return integer
     */
    public function count(string $collection, QueryObject $query): int
    {
        return isset($this->data[$collection]) ? count($this->read($collection, $query)) : 0;
    }

    /**
     * Factory method
     *
     * @param array $conditions
     * @return Criteria
     */
    private function createCriteria(array $conditions): Criteria
    {
        return new Criteria($conditions);
    }

    /**
     * A mysql style order by in theory
     * @internal array_multisort error Array sizes are inconsistent - Means field did not exist
     * @param array $data
     * @param array $sort  ['id' => 'ASC']
     * @return array
     */
    private function sortData(array $data, array $sort): array
    {
        $args = [];

        foreach ($sort as $column => $order) {
            $this->checkKeyExists($column, $data);
            $args[] = array_column($data, $column);
            $args[] = strtoupper($order) === 'DESC' ? SORT_DESC : SORT_ASC;
        }
        $args[] = $data;

        array_multisort(...$args);

        return array_pop($args);
    }

    private function checkKeyExists(string $key, array $data)
    {
        $result = ! empty($data);
        foreach ($data as $row) {
            $result = $result && isset($row[$key]);
        }

        if (! $result) {
            throw new RuntimeException(sprintf('The key `%s` does not exist in one or more rows of the data', $key));
        }
    }
}
