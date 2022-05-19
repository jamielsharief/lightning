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
use InvalidArgumentException;
use Lightning\Utility\Collection;
use Lightning\Entity\EntityInterface;
use Lightning\DataMapper\Event\AfterFindEvent;
use Lightning\DataMapper\Event\AfterSaveEvent;
use Lightning\DataMapper\Event\BeforeFindEvent;
use Lightning\DataMapper\Event\BeforeSaveEvent;
use Lightning\DataMapper\Event\InitializeEvent;
use Lightning\DataMapper\Event\AfterCreateEvent;
use Lightning\DataMapper\Event\AfterDeleteEvent;
use Lightning\DataMapper\Event\AfterUpdateEvent;
use Lightning\DataMapper\Event\BeforeCreateEvent;
use Lightning\DataMapper\Event\BeforeDeleteEvent;
use Lightning\DataMapper\Event\BeforeUpdateEvent;
use Lightning\Entity\Callback\AfterLoadInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Lightning\Entity\Callback\AfterDeleteInterface;
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
      * PSR-14 Event Dispatcher
      *
      * @var EventDispatcherInterface|null
      */
    protected ?EventDispatcherInterface $eventDispatcher = null;

    /**
     * Constructor
     */
    public function __construct(DataSourceInterface $dataSource, ?EventDispatcherInterface $eventDispatcher = null)
    {
        $this->dataSource = $dataSource;
        $this->eventDispatcher = $eventDispatcher;

        $this->initialize();
        $this->dispatchEvent(new InitializeEvent($this));
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
     * Inserts an Entity into the database
     */
    protected function create(EntityInterface $entity): bool
    {
        $this->dispatchEvent(new BeforeCreateEvent($this, $entity));

        $data = array_intersect_key($this->mapEntityToData($entity), array_flip($this->fields));

        $result = $this->dataSource->create($this->table, $data);

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

            $this->dispatchEvent(new AfterCreateEvent($this, $entity));
        }

        return $result;
    }

    /**
     * Saves an Entity
     */
    public function save(EntityInterface $entity): bool
    {
        $this->dispatchEvent(new BeforeSaveEvent($this, $entity));

        $result = $entity->isNew() ? $this->create($entity) : $this->update($entity);

        if ($result) {
            $this->dispatchEvent(new AfterSaveEvent($this, $entity));
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

        $this->dispatchEvent(new BeforeFindEvent($this, $query));

        return $this->dataSource->count($this->table, $query);
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

        foreach ($collection as $row) {

            // Create list
            $key = $row[$keyField] ?? null;

            if (! $valueField) {
                $result[] = $key;

                continue;
            }

            if ($groupField) {
                $group = $row[$groupField] ?? null;
                if (! isset($result[$group])) {
                    $result[$group] = [];
                }
                $result[$group][$key] = $row[$valueField] ?? null;

                continue;
            }

            $result[$key] = $row[$valueField] ?? null;
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
    protected function createCollection(array $items = []): Collection
    {
        return new Collection($items);
    }

    /**
     * Reads from the datasource
     */
    protected function read(QueryObject $query, bool $mapResult = true): Collection
    {
        $this->dispatchEvent(new BeforeFindEvent($this, $query));

        if ($this->fields && ! $query->getOption('fields')) {
            $query->setOption('fields', $this->fields);
        }

        $collection = $this->createCollection($this->dataSource->read($this->table, $query));
        if ($collection->isEmpty()) {
            return $collection;
        }

        $this->dispatchEvent(new AfterFindEvent($this, $collection, $query));

        if ($mapResult) {
            foreach ($collection as $index => $row) {
                $entity = $this->mapDataToEntity($row->toArray());
                $entity->markPersisted(true);
                $collection[$index] = $entity;

                if ($entity instanceof AfterLoadInterface) {
                    $entity->afterLoad();
                }
            }
        }

        return $collection;
    }

    /**
     * Updates an Entity
     */
    public function update(EntityInterface $entity): bool
    {
        $this->dispatchEvent(new BeforeUpdateEvent($this, $entity));

        $row = array_intersect_key($this->mapEntityToData($entity), array_flip($this->fields));
        $query = $this->createQueryObject($this->getConditionsFromState($row));

        $result = $this->dataSource->update($this->table, $query, $row) === 1;
        if ($result) {
            $this->dispatchEvent(new AfterUpdateEvent($this, $entity));
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
     * Updates an Entity
     */
    public function delete(EntityInterface $entity): bool
    {
        $this->dispatchEvent(new BeforeDeleteEvent($this, $entity));

        $row = $this->mapEntityToData($entity);
        $query = $this->createQueryObject($this->getConditionsFromState($row));

        $result = $this->dataSource->delete($this->table, $query) === 1;

        if ($result) {
            if ($entity instanceof AfterDeleteInterface) {
                $entity->afterDelete();
            }
            $this->dispatchEvent(new AfterDeleteEvent($this, $entity));
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

    /**
     * Dispatches an Event using the PSR-14 Event Dispatcher if available
     */
    protected function dispatchEvent(object $event): ?object
    {
        return $this->eventDispatcher ? $this->eventDispatcher->dispatch($event) : null;
    }
}
