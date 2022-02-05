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

namespace Lightning\Orm;

use LogicException;
use ReflectionClass;
use Lightning\DataMapper\ResultSet;
use Lightning\DataMapper\QueryObject;
use Lightning\Entity\EntityInterface;
use Lightning\DataMapper\AbstractDataMapper;
use Lightning\DataMapper\DataSourceInterface;

/**
 * AbstractORM
 *
 * @internal Joins are not used since all queries should go through hooks and events, using joins would escape these and when going deep you will
 * have to do additional querieis anyway. Also by not using joins then not tying datasource to only relational databases.
 */
abstract class AbstractOrm extends AbstractDataMapper
{
    /**
      * This also assumes $this->profile is the Profile model injected during construction
      *
      * @example
      *  'profile' => [
      *       'className' => Profile::class
      *       'foreignKey' => 'user_id', // in other table
      *       'dependent' => false
      *   ]
      *
      * @var array
      */
    protected array $hasOne = [];

    /**
     * @example
     *   'user' => [
     *       'className' => User::class
     *       'foreignKey' => 'user_id' // in this table
     *   ]
     *
     * @var array
     */

    protected array $belongsTo = [];

    /**
     * @example
     *
     *  'comments' => [
     *      'className' => User::class
     *      'foreignKey' => 'user_id', // in other table,
     *      'dependent' => false
     *  ]
     *
     * @var array
     */
    protected array $hasMany = [];

    /**
     * @example
     *
     *  'tags' => [
     *      'className' => User::class
     *      'table' => 'tags_users',
     *      'foreignKey' => 'user_id',
     *      'localKey' => 'tag_id',
     *      'dependent' => true
     * ]
     *
     * @var array
     */
    protected array $hasAndBelongsToMany = [];

    protected array $associations = ['belongsTo','hasMany','hasOne','hasAndBelongsToMany'];

    /**
     * Constructor
     *
     * @param DataSourceInterface $dataSource
     */
    public function __construct(DataSourceInterface $dataSource)
    {
        parent::__construct($dataSource);

        $this->checkAssociationDefinitions();
    }

    /**
     * Reads from the DataSource
     *
     * @param QueryObject $query
     * @param boolean $mapResult
     * @return ResultSet
     */
    protected function read(QueryObject $query, bool $mapResult = true): ResultSet
    {
        $resultSet = parent::read($query, $mapResult);

        return $resultSet->isEmpty() ? $resultSet : $this->loadRelatedData($resultSet, $query);
    }

    /**
     * TODO: onDelete or doDelete or something
     *
     * @param EntityInterface $entity
     * @return boolean
     */
    public function delete(EntityInterface $entity): bool
    {
        if ($result = parent::delete($entity) && is_string($this->primaryKey)) {
            $id = $entity->toArray()[$this->primaryKey] ?? null;
            if ($id) {
                $this->deleteDependent($id);
            }
        }

        return $result;
    }

    /**
     * Check array defintions and add some defaults
     *
     * @return void
     */
    private function checkAssociationDefinitions(): void
    {
        foreach ($this->associations as $assoc) {
            foreach ($this->$assoc as $property => &$config) {
                if (empty($config['foreignKey'])) {
                    throw new LogicException(sprintf('%s `%s` is missing foreignKey', $property, $assoc));
                }
                if (empty($config['className'])) {
                    throw new LogicException(sprintf('%s `%s` is missing class', $property, $assoc));
                }

                // TODO:
                if (empty($config['alias'])) {
                    $config['alias'] = (new ReflectionClass($config['className']))->getShortName();
                }

                if ($assoc === 'hasAndBelongsToMany') {
                    if (empty($config['table'])) {
                        throw new LogicException(sprintf('hasAndBelongsToMany `%s` requires a table key', $property));
                    }
                    if (empty($config['localKey'])) {
                        throw new LogicException(sprintf('hasAndBelongsToMany `%s` is missing localKey', $property));
                    }
                }
                $config['association'] = $assoc;
            }
        }
    }

    /**
     * Loads the related data
     *
     * @param ResultSet $resultSet
     * @param QueryObject $query
     * @return ResultSet
     */
    private function loadRelatedData(ResultSet $resultSet, QueryObject $query): ResultSet
    {
        $options = $query->getOptions();

        if (isset($options['with'])) {
            $this->loadRelatedBelongsTo($resultSet, $options);
            $this->loadRelatedHasOne($resultSet, $options);
            $this->loadRelatedHasMany($resultSet, $options);
            $this->loadRelatedHasAndBelongsToMany($resultSet, $options);
        }

        return $resultSet;
    }

    /**
     * Load BelongsTo data
     *
     * @param ResultSet $resultSet
     * @param array $options
     * @return void
     */
    private function loadRelatedBelongsTo(ResultSet $resultSet, array $options): void
    {

        // Preload associations
        $associations = $this->loadAssociations('belongsTo', $options);
        if (! $associations) {
            return;
        }

        $load = array_fill_keys(array_keys($associations), []);

        // Fetch IDS
        foreach ($resultSet as $row) {
            foreach ($associations as $property => $config) {
                $foreignKey = $config['foreignKey'];
                $load[$property][] = $row[$foreignKey];
            }
        }

        // Load Records
        $records = array_fill_keys(array_keys($associations), []);
        foreach ($associations as $property => $config) {
            $ids = $load[$property];

            $relatedRecord = new ResultSet($this->{$config['alias']}->findAllBy([$this->primaryKey => $ids]));
            $records[$property] = $relatedRecord->indexBy(function ($entity) {
                return $entity->id;
            });
            unset($load[$property], $relatedRecord);
        }

        // Add to records
        foreach ($resultSet as $row) {
            $id = $row[$foreignKey];
            foreach ($associations as $property => $config) {
                $foreignKey = $config['foreignKey'];
                $hasMatch = $id && isset($records[$property][$id]);
                $row[$property] = $hasMatch ? $records[$property][$id] : null;
            }
        }

        unset($records);
    }

    /**
     * Load HasOne data
     *
     * @param ResultSet $resultSet
     * @param array $options
     * @return void
     */
    private function loadRelatedHasOne(ResultSet $resultSet, array $options): void
    {
        // Preload associations
        $associations = $this->loadAssociations('hasOne', $options);
        if (! $associations) {
            return;
        }

        $load = array_fill_keys(array_keys($associations), []);

        // Fetch IDS
        foreach ($resultSet as $row) {
            $id = $row[$this->primaryKey];
            foreach ($associations as $property => $config) {
                $load[$property][] = $id;
            }
        }

        // Load Records
        $records = array_fill_keys(array_keys($associations), []);
        foreach ($associations as $property => $config) {
            $ids = $load[$property];
            $foreignKey = $config['foreignKey'];
            $relatedRecord = new ResultSet($this->{$config['alias']}->findAllBy([$foreignKey => $ids]));
            $records[$property] = $relatedRecord->indexBy(function ($entity) use ($foreignKey) {
                return $entity->$foreignKey;
            });

            unset($ids,$load[$property],$relatedRecord);
        }

        // Add to records
        foreach ($resultSet as $row) {
            $id = $row[$this->primaryKey];
            foreach ($associations as $property => $config) {
                $hasMatch = $id && isset($records[$property][$id]);
                $row[$property] = $hasMatch ? $records[$property][$id] : null;
            }
        }

        unset($records);
    }

    /**
     * Fetches the hasManyData
     *
     * @param ResultSet $resultSet
     * @param array $options
     * @return void
     */
    private function loadRelatedHasMany(ResultSet $resultSet, array $options): void
    {

        // Preload associations
        $associations = $this->loadAssociations('hasMany', $options);
        if (! $associations) {
            return;
        }

        $load = array_fill_keys(array_keys($associations), []);

        // Fetch IDS
        foreach ($resultSet as $row) {
            $id = $row[$this->primaryKey];
            foreach ($associations as $property => $config) {
                $load[$property][] = $id;
            }
        }

        // Author is loading articles,

        // Load Records
        $records = array_fill_keys(array_keys($associations), []);
        foreach ($associations as $property => $config) {
            $ids = $load[$property];
            $foreignKey = $config['foreignKey'];
            $relatedRecords = new ResultSet($this->{$config['alias']}->findAllBy([$foreignKey => $ids]));

            $records[$property] = $relatedRecords->groupBy(function ($entity) use ($foreignKey) {
                return $entity->$foreignKey;
            })->toArray();

            unset($ids,$load[$property], $relatedRecords);
        }

        // Add to records
        foreach ($resultSet as $row) {
            $id = $row[$this->primaryKey];
            foreach ($associations as $property => $config) {
                $hasMatch = isset($records[$property][$id]);
                $row[$property] = $hasMatch ? $records[$property][$id] : [];
            }
        }
        unset($records);
    }

    /**
    * Fetches the hasAndBelongsToMany
    *
    * @param ResultSet $resultSet
    * @param array $options
    * @return void
    */
    private function loadRelatedHasAndBelongsToMany(ResultSet $resultSet, array $options): void
    {

        // Preload associations
        $associations = $this->loadAssociations('hasAndBelongsToMany', $options);
        if (! $associations) {
            return;
        }

        $keys = array_keys($associations);
        $load = array_fill_keys($keys, []);
        $recordMap = array_fill_keys(array_keys($associations), []);
        $records = array_fill_keys(array_keys($associations), []);

        // Fetch IDS
        foreach ($resultSet as $row) {
            $id = $row[$this->primaryKey];
            foreach ($associations as $property => $config) {
                $load[$property][] = $id;
            }
        }

        // HOW TO QUERY // posts_tags
        // Load Records
        foreach ($associations as $property => $config) {
            $ids = $load[$property];
            $foreignKey = $config['foreignKey'];

            $result = $this->dataSource->read($config['table'], new QueryObject([$foreignKey => $ids]));

            $localKey = $config['localKey'];

            foreach ($result as $record) {
                $ids[] = $record[$localKey];
                $id = $record[$foreignKey];
                $recordMap[$property][$id][] = $record[$localKey];
            }

            $primaryKey = $this->{$config['alias']}->getPrimaryKey()[0];
            $relatedRecords = new ResultSet($this->{$config['alias']}->findAllBy([$primaryKey => $ids]));

            $records[$property] = $relatedRecords->indexBy(function ($entity) use ($primaryKey, $recordMap) {
                return $entity->$primaryKey;
            });

            unset($ids,$load[$property],$relatedRecords);
        }

        foreach ($resultSet as $row) {
            $row[$property] = [];
            $id = $row[$this->primaryKey];
            foreach ($associations as $property => $config) {
                $matched = [];
                if (isset($recordMap[$property][$id])) {
                    foreach ($recordMap[$property][$id] as $relatedId) {
                        if (isset($records[$property][$relatedId])) {
                            $matched[] = $records[$property][$relatedId];
                        }
                    }
                }
                $row[$property] = $matched;
            }
        }

        unset($records);
    }

    /**
     * Loads the request type of associations that are being requested
     *
     * @param string $type
     * @param array $options
     * @return array
     */
    private function loadAssociations(string $type, array $options): array
    {
        $associations = [];

        foreach ($this->$type as $property => $config) {
            if (in_array($property, $options['with'])) {
                $associations[$property] = $config;

                // TODO: This needs to be managed to prevent recursion
                if (! isset($this->{$config['alias']})) {
                    $this->{$config['alias']} = new $config['className']($this->dataSource);
                }
            }
        }

        return $associations;
    }

    private function loadMapper(string $class, string $alias): AbstractDataMapper
    {
        if (! isset($this->{$alias})) {
            $this->{$alias} = new $class($this->dataSource);
        }

        return $this->{$alias};
    }

    /**
     * Deletes dependent records for the hasOne, hasMany and hasAndBelongsToMany associations
     *
     * @param string|integer $id
     * @return void
     */
    private function deleteDependent($id): void
    {
        // User has one profile, user_id in other table
        foreach (['hasOne','hasMany'] as $assoc) {
            foreach ($this->$assoc as $config) {
                $mapper = $this->loadMapper($config['className'], $config['alias']);
                foreach ($mapper->findAllBy([$config['foreignKey'] => $id]) as $entity) {
                    $mapper->delete($entity);
                }
            }
        }

        foreach ($this->hasAndBelongsToMany as $config) {
            $this->dataSource->delete($config['table'], new QueryObject([$config['foreignKey'] => $id]));
        }
    }
}
