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

use Lightning\Utility\Collection;
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
    /**
     * Constructor
     */
    public function __construct(protected AbstractDataMapper $mapper)
    {
    }

    /**
     * Finds a single Entity
     */
    public function find(?QueryObject $query = null): ?EntityInterface
    {
        return $this->mapper->find($query);
    }

    /**
     * Finds multiple Entities
     * @return Collection|EntityInterface[]
     */
    public function findAll(?QueryObject $query = null): Collection
    {
        return $this->mapper->findAll($query);
    }

    /**
     * Finds the count of Entities that match the query
     */
    public function findCount(?QueryObject $query = null): int
    {
        return $this->mapper->findCount($query);
    }

    /**
     * Finds a list using the query
     *
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
     */
    public function findBy(array $criteria, array $options = []): ?EntityInterface
    {
        return $this->mapper->findBy($criteria, $options);
    }

    /**
     * Finds multiple instances
     *
     * @return Collection|EntityInterface[]
     */
    public function findAllBy(array $criteria, array $options = []): Collection
    {
        return $this->mapper->findAllBy($criteria, $options);
    }

    /**
     * Finds the count of the number of instances
     */
    public function findCountBy(array $criteria, array $options = []): int
    {
        return $this->mapper->findCountBy($criteria, $options);
    }

    /**
     * Finds a list
     *
     * @param array $fields
     *  - keyField: defaults to primary key if it is a string
     *  - valueField: optional
     *  - groupField: optional
     */
    public function findListBy(array $criteria, array $fields = [], array $options = []): array
    {
        return $this->mapper->findListBy($criteria, $fields, $options);
    }

    /**
     * Saves an Entity
     */
    public function save(EntityInterface $entity): bool
    {
        return $this->mapper->save($entity);
    }

    /**
     * Saves multiple Entities
     */
    public function saveMany(iterable $entities): bool
    {
        return $this->mapper->saveMany($entities);
    }

    /**
     * Deletes an Entity
     */
    public function delete(EntityInterface $entity): bool
    {
        return $this->mapper->delete($entity);
    }

    /**
     * Deletes multiple entities
     */
    public function deleteMany(iterable $entities): bool
    {
        return $this->mapper->deleteMany($entities);
    }

    /**
     * Deletes all Entities that match the query
     */
    public function deleteAll(QueryObject $query): int
    {
        return $this->mapper->deleteAll($query);
    }

    /**
     * Deletes all Entities that match the criteria
     */
    public function deleteAllBy(array $criteria, array $options = []): int
    {
        return $this->mapper->deleteAllBy($criteria, $options);
    }

    /**
     * Updates all the entities that match the query
     */
    public function updateAll(QueryObject $query, array $data): int
    {
        return $this->mapper->updateAll($query, $data);
    }

    /**
     * Updates all entities that match the criteria
     */
    public function updateAllBy(array $criteria, array $data, array $options = []): int
    {
        return $this->mapper->updateAllBy($criteria, $data, $options);
    }

    /**
     * Creates a new Query object
     */
    public function createQueryObject(array $criteria = [], array $options = []): QueryObject
    {
        return new QueryObject($criteria, $options);
    }

    /**
     * Gets the DataSource for this Repository
     */
    public function getDataSource(): DataSourceInterface
    {
        return $this->mapper->getDataSource();
    }

    /**
     * Gets the Data Mapper
     */
    public function getDataMapper(): AbstractDataMapper
    {
        return $this->mapper;
    }
}
