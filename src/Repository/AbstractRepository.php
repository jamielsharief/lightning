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

namespace Lightning\Repository;

use Lightning\DataMapper\ResultSet;
use Lightning\DataMapper\QueryObject;
use Lightning\Entity\EntityInterface;
use Lightning\DataMapper\AbstractDataMapper;
use Lightning\DataMapper\DataSourceInterface;

/**
 * Repository
 *
 * This is an additional layer on top of the DataMapper
 *
 * @link https://martinfowler.com/eaaCatalog/repository.html
 */
abstract class AbstractRepository
{
    protected AbstractDataMapper $mapper;

    /**
     * Constructor
     *
     * @param AbstractDataMapper $mapper
     */
    public function __construct(AbstractDataMapper $mapper)
    {
        $this->mapper = $mapper;

        $this->initialize();
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    protected function initialize(): void
    {
    }

    /**
     * Finds a single Entity
     *
     * @param QueryObject|null $query
     * @return EntityInterface|null
     */
    public function find(?QueryObject $query = null): ?EntityInterface
    {
        return $this->mapper->find($query);
    }

    /**
     * Finds multiple Entities
     * @return ResultSet|EntityInterface[]
     */
    public function findAll(?QueryObject $query = null): ResultSet
    {
        return $this->mapper->findAll($query);
    }

    /**
     * Finds the count of Entities that match the query
     *
     *
     * @param QueryObject|null $query
     * @return integer
     */
    public function findCount(?QueryObject $query = null): int
    {
        return $this->mapper->findCount($query);
    }

    /**
     * Finds a list using the query
     *
     * @param QueryObject|null $query
     * @param array $fields
     *  - keyField: defaults to primary key if it is a string
     *  - valueField: optional
     *  - groupField: optional
     * @return array
     */
    public function findList(?QueryObject $query = null, array $fields = []): array
    {
        return $this->mapper->findList($query, $fields);
    }

    /**
     * Returns a single instance
     *
     * @param array $criteria
     * @param array $options
     * @return EntityInterface|null
     */
    public function findBy(array $criteria, array $options = []): ?EntityInterface
    {
        return $this->mapper->findBy($criteria, $options);
    }

    /**
     * Finds multiple instances
     *
     * @param array $criteria
     * @param array $options
     * @return ResultSet|EntityInterface[]
     */
    public function findAllBy(array $criteria, array $options = []): ResultSet
    {
        return $this->mapper->findAllBy($criteria, $options);
    }

    /**
     * Finds the count of the number of instances
     *
     * @param array $criteria
     * @param array $options
     * @return integer
     */
    public function findCountBy(array $criteria, array $options = []): int
    {
        return $this->mapper->findCountBy($criteria, $options);
    }

    /**
     * Finds a list
     *
     * @param array $criteria
     * @param array $fields
     *  - keyField: defaults to primary key if it is a string
     *  - valueField: optional
     *  - groupField: optional
     * @param array $options
     * @return array
     */
    public function findListBy(array $criteria, array $fields = [], array $options = []): array
    {
        return $this->mapper->findListBy($criteria, $fields, $options);
    }

    /**
     * Saves an Entity
     *
     * @param EntityInterface $entity
     * @return boolean
     */
    public function save(EntityInterface $entity): bool
    {
        return $this->mapper->save($entity);
    }

    /**
     * Saves multiple Entities
     *
     * @param iterable $entities
     * @return boolean
     */
    public function saveMany(iterable $entities): bool
    {
        return $this->mapper->saveMany($entities);
    }

    /**
     * Deletes an Entity
     *
     * @param EntityInterface $entity
     * @return boolean
     */
    public function delete(EntityInterface $entity): bool
    {
        return $this->mapper->delete($entity);
    }

    /**
     * Deletes multiple entities
     *
     * @param iterable $entities
     * @return boolean
     */
    public function deleteMany(iterable $entities): bool
    {
        return $this->mapper->deleteMany($entities);
    }

    /**
     * Deletes all Entities that match the query
     *
     * @param QueryObject $query
     * @return integer
     */
    public function deleteAll(QueryObject $query): int
    {
        return $this->mapper->deleteAll($query);
    }

    /**
     * Deletes all Entities that match the criteria
     *
     * @param array $criteria
     * @param array $options
     * @return integer
     */
    public function deleteAllBy(array $criteria, array $options = []): int
    {
        return $this->mapper->deleteAllBy($criteria, $options);
    }

    /**
     * Updates all the entities that match the query
     *
     * @param QueryObject $query
     * @param array $data
     * @return integer
     */
    public function updateAll(QueryObject $query, array $data): int
    {
        return $this->mapper->updateAll($query, $data);
    }

    /**
     * Updates all entities that match the criteria
     *
     * @param array $data
     * @param array $criteria =[], array $options = []
     * @return integer
     */
    public function updateAllBy(array $criteria, array $data, array $options = []): int
    {
        return $this->mapper->updateAllBy($criteria, $data, $options);
    }

    /**
     * Creates a new Query object
     *
     * @param array $criteria
     * @param array $options
     * @return QueryObject
     */
    public function createQueryObject(array $criteria = [], array $options = []): QueryObject
    {
        return new QueryObject($criteria, $options);
    }

    /**
     * Gets the DataSource for this Repository
     *
     * @return DataSourceInterface
     */
    public function getDataSource(): DataSourceInterface
    {
        return $this->mapper->getDataSource();
    }
}
