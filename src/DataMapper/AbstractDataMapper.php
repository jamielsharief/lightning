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

namespace Lightning\DataMapper;

use ReflectionProperty;
use BadMethodCallException;
use Lightning\Database\Row;
use InvalidArgumentException;
use Lightning\Utility\Collection;
use Lightning\Entity\EntityInterface;
use Lightning\DataMapper\Exception\EntityNotFoundException;

abstract class AbstractDataMapper
{
    protected DataSourceInterface $dataSource;

    /**
     * Primary Key
     *
     * @var array<string>|string
     */
    protected $primaryKey = 'id';
    protected string $table = 'none';

    /**
     * The default fields to select, if this is empty then a wildcard will be used
     */
    protected array $fields = [];

    /**
     * Constructor
     */
    public function __construct(DataSourceInterface $dataSource)
    {
        $this->dataSource = $dataSource;

        $this->initialize();
    }

    /**
     * A hook that is called when the object is created
     */
    protected function initialize(): void
    {
    }

    /**
     * Gets primary key used by this Mapper
     */
    public function getPrimaryKey(): array
    {
        return (array) $this->primaryKey;
    }

    /**
     * Gets the DataSource for this Mapper
     */
    public function getDataSource(): DataSourceInterface
    {
        return $this->dataSource;
    }

    /**
     * Before create callback
     */
    protected function beforeCreate(EntityInterface $entity): bool
    {
        return true;
    }

    /**
     * After create callback
     */
    protected function afterCreate(EntityInterface $entity): void
    {
    }

    /**
     * Before update callback
     */
    protected function beforeUpdate(EntityInterface $entity): bool
    {
        return true;
    }

    /**
     * after update callback
     */
    protected function afterUpdate(EntityInterface $entity): void
    {
    }

    /**
     * Before save callback
     */
    protected function beforeSave(EntityInterface $entity): bool
    {
        return true;
    }

    /**
     * After save callback
     */
    protected function afterSave(EntityInterface $entity): void
    {
    }

    /**
     * Before delete callback
     */
    protected function beforeDelete(EntityInterface $entity): bool
    {
        return true;
    }

    /**
     * after delete callback
     */
    protected function afterDelete(EntityInterface $entity): void
    {
    }

    /**
     * before find callback
     */
    protected function beforeFind(QueryObject $query): bool
    {
        return true;
    }

    /**
     * After find callback
     */
    protected function afterFind(Collection $collection, QueryObject $query): void
    {
    }

    /**
     * Inserts an Entity into the database
     */
    protected function create(EntityInterface $entity): bool
    {
        if (! $this->beforeCreate($entity)) {
            return false;
        }

        $row = array_intersect_key($this->mapEntityToData($entity), array_flip($this->fields));;
        $result = $this->dataSource->create($this->table, $row);

        if ($result) {
            $entity->markPersisted(true);

            // Add generated ID
            $id = $this->dataSource->getGeneratedId();
            if ($id && is_string($this->primaryKey)) {
                $reflectionProperty = new ReflectionProperty($entity, $this->primaryKey);
                if ($reflectionProperty->isPrivate()) {
                    $reflectionProperty->setAccessible(true); // Only required for PHP 8.0 and lower
                }
                $reflectionProperty->setValue($entity, $id);
            }

            $this->afterCreate($entity);
        }

        return $result;
    }

    /**
     * Saves an Entity
     */
    public function save(EntityInterface $entity): bool
    {
        if (! $this->beforeSave($entity)) {
            return false;
        }

        $result = $entity->isNew() ? $this->create($entity) : $this->update($entity);

        if ($result) {
            $this->afterSave($entity);
        }

        return $result;
    }

    /**
     * Gets an Entity or throws an exception
     * @throws EntityNotFoundException
     */
    public function get(QueryObject $query): EntityInterface
    {
        $result = $this->find($query);

        if (! $result) {
            throw new EntityNotFoundException('Entity Not Found');
        }

        return $result;
    }

    /**
     * Finds a single Entity
     */
    public function find(?QueryObject $query = null): ?EntityInterface
    {
        $query = $query ?? $this->createQueryObject();

        return $this->read($query->setOption('limit', 1))->get(0);
    }

    /**
     * Finds multiple Entities
     * @return Collection|EntityInterface[]
     */
    public function findAll(?QueryObject $query = null): Collection
    {
        $query = $query ?? $this->createQueryObject();

        return $this->read($query);
    }

    /**
     * Finds the count of Entities that match the query
     */
    public function findCount(?QueryObject $query = null): int
    {
        $query = $query ?? $this->createQueryObject();

        return $this->beforeFind($query) === false ? 0 : $this->dataSource->count($this->table, $query);
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
        $query = $query ?? $this->createQueryObject();

        $keyField = $fields['keyField'] ?? (is_string($this->primaryKey) ? $this->primaryKey : null);
        if (! $keyField) {
            throw new InvalidArgumentException('Cannot determine primary key');
        }

        return $this->convertCollectionToList(
            $this->read($query, false),
            $keyField, $fields['valueField'] ?? null, $fields['groupField'] ?? null
        );
    }

    /**
     * Converts the Collection to a list
     */
    private function convertCollectionToList(Collection $collection, string $keyField, ?string $valueField = null, ?string $groupField = null): array
    {
        $result = [];

        // grouped list
        if ($groupField && $valueField && $keyField) {
            $result = $collection->reduce(function (array $entitites, Row $row) use ($keyField, $valueField, $groupField) {
                $entitites[$row[$groupField] ?? null][$row[$keyField] ?? null] = $row[$valueField] ?? null;

                return $entitites;
            }, []);
        }

        // key value list
        elseif ($valueField && $keyField) {
            $result = $collection->reduce(function (array $entitites, Row $row) use ($keyField, $valueField) {
                $entitites[$row[$keyField] ?? null] = $row[$valueField] ?? null;

                return $entitites;
            }, []);
        }

        // value list
        elseif ($keyField) {
            $result = $collection->reduce(function (array $entitites, Row $row) use ($keyField) {
                $entitites[] = $row[$keyField] ?? null;

                return $entitites;
            }, []);
        }

        return $result;
    }

    /**
     * Gets an Entity or throws an exception
     */
    public function getBy(array $criteria = [], array $options = []): EntityInterface
    {
        return $this->get($this->createQueryObject($criteria, $options));
    }

    /**
     * Returns a single instance
     *
     * @param array $options Options vary between datasources, but the following should be supported
     *  - limit
     *  - offset
     *  - sort
     * @return EntityInterface|null
     */
    public function findBy(array $criteria = [], array $options = []): ?EntityInterface
    {
        return $this->find($this->createQueryObject($criteria, $options));
    }

    /**
     * Finds multiple instances
     * @return Collection|EntityInterface[]
     */
    public function findAllBy(array $criteria, array $options = []): Collection
    {
        return $this->findAll($this->createQueryObject($criteria, $options));
    }

    /**
     * Finds the count of the number of instances
     */
    public function findCountBy(array $criteria, array $options = []): int
    {
        return $this->findCount($this->createQueryObject($criteria, $options));
    }

    /**
     * Finds a list
     * @param array $fields
     *  - keyField: defaults to primary key if it is a string
     *  - valueField: optional
     *  - groupField: optional
     */
    public function findListBy(array $criteria, array $fields = [], array $options = []): array
    {
        return $this->findList($this->createQueryObject($criteria, $options), $fields);
    }

    /**
     * Factory method
     */
    public function createCollection(array $items = []): Collection
    {
        return new Collection($items);
    }

    /**
     * Reads from the datasource
     */
    protected function read(QueryObject $query, bool $mapResult = true): Collection
    {
        if (! $this->beforeFind($query)) {
            return $this->createCollection();
        }

        if ($this->fields && ! $query->getOption('fields')) {
            $query->setOption('fields', $this->fields);
        }

        $collection = $this->createCollection($this->dataSource->read($this->table, $query));
        if ($collection->isEmpty()) {
            return $collection;
        }

        $this->afterFind($collection, $query);

        if ($mapResult) {
            foreach ($collection as $index => $row) {
                $entity = $this->mapDataToEntity($row->toArray());
                $entity->markPersisted(true);
                $collection[$index] = $entity;
            }
        }

        return $collection;
    }

    /**
     * Updates an Entity
     */
    public function update(EntityInterface $entity): bool
    {
        if (! $this->beforeUpdate($entity)) {
            return false;
        }

        $row = array_intersect_key($this->mapEntityToData($entity), array_flip($this->fields));
        $query = $this->createQueryObject($this->getConditionsFromState($row));

        $result = $this->dataSource->update($this->table, $query, $row) === 1;

        if ($result) {
            $this->afterUpdate($entity);
        }

        return $result;
    }

    /**
     * Updates records that match query with the data provided but no events or hooks will be triggered
     */
    public function updateAll(QueryObject $query, array $data): int
    {
        if (empty($data)) {
            throw new InvalidArgumentException('Data cannot be empty');
        }

        return $this->dataSource->update($this->table, $query, $data);
    }

    /**
     * Deletes records that match the query but no events or hooks will be triggered
     */
    public function deleteAll(QueryObject $query): int
    {
        return $this->dataSource->delete($this->table, $query);
    }

    /**
     * Saves a collection of entities
     */
    public function saveMany(iterable $entities): bool
    {
        foreach ($entities as $entity) {
            if (! $this->save($entity)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Deletes a collection of entities
     */
    public function deleteMany(iterable $entities): bool
    {
        foreach ($entities as $entity) {
            if (! $this->delete($entity)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Creates a new Query object
     */
    public function createQueryObject(array $criteria = [], array $options = []): QueryObject
    {
        return new QueryObject($criteria, $options);
    }

    /**
     * Deletes an entity
     */
    public function delete(EntityInterface $entity): bool
    {
        if (! $this->beforeDelete($entity)) {
            return false;
        }

        $row = $this->mapEntityToData($entity);
        $query = $this->createQueryObject($this->getConditionsFromState($row));

        $result = $this->dataSource->delete($this->table, $query) === 1;

        if ($result) {
            $this->afterDelete($entity);
        }

        return $result;
    }

    /**
     * Deletes records that match the criteria but no events or hooks will be triggered
     */
    public function deleteAllBy(array $criteria, array $options = []): int
    {
        return $this->deleteAll($this->createQueryObject($criteria, $options));
    }

    /**
     * Updates records that match criteria with the data provided but no events or hooks will be triggered
     */
    public function updateAllBy(array $criteria, array $data, array $options = []): int
    {
        return $this->updateAll($this->createQueryObject($criteria, $options), $data);
    }

    /**
     * Maps state array to entity
     */
    abstract public function mapDataToEntity(array $state): EntityInterface;

    /**
     * Converts the entity into a database row
     */
    public function mapEntityToData(EntityInterface $entity): array
    {
        return $entity->toState();
    }

    /**
     * Creates an Entity from an array using mapping.
     */
    public function createEntity(array $data = [], array $options = []): EntityInterface
    {
        $options += ['fields' => $this->fields,'persisted' => false];
        if ($options['fields']) {
            $data = array_intersect_key($data, array_flip((array) $options['fields']));
        }

        $entity = $this->mapDataToEntity($data);
        $entity->markPersisted($options['persisted']);

        return $entity;
    }

    /**
     * Create a collection of Entities
     */
    public function createEntities(array $data, array $options = []): iterable
    {
        return array_map(function ($row) use ($options) {
            return $this->createEntity($row, $options);
        }, $data);
    }

    /**
     * Creates the conditions array from a particular entity
     */
    protected function getConditionsFromState(array $state): array
    {
        $conditions = [];

        foreach ((array) $this->primaryKey as $key) {
            if (! isset($state[$key])) {
                throw new BadMethodCallException(sprintf('Primary key `%s` has no value', $key));
            }
            $conditions[$key] = $state[$key];
        }

        return $conditions;
    }
}
