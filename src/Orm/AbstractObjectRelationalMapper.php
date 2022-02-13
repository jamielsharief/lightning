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
use Lightning\Entity\Entity;
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
 *
 */
abstract class AbstractObjectRelationalMapper extends AbstractDataMapper
{
    protected MapperManager $mapperManager;

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
     *      'foreignKey' => 'user_id', // in other table
      *     'dependent' => false
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
     *      'joinTable' => 'tags_users',
     *      'foreignKey' => 'tag_id',
     *      'otherForeignKey' => 'user_id', // the foreignKey for the associated model
     *      'dependent' => true
     * ]
     *
     * @var array
     */
    protected array $belongsToMany = [];

    protected array $associations = ['belongsTo','hasMany','hasOne','belongsToMany'];

    /**
     * Constructor
     *
     * @param DataSourceInterface $dataSource
     */
    public function __construct(DataSourceInterface $dataSource, MapperManager $mapperManager)
    {
        $this->mapperManager = $mapperManager;

        parent::__construct($dataSource);
        $this->initializeOrm();
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

        return $query->getOption('with') && $resultSet->isEmpty() === false ? $this->loadRelatedData($resultSet, $query) : $resultSet;
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
    private function initializeOrm(): void
    {
        foreach ($this->associations as $assoc) {
            foreach ($this->$assoc as $property => &$config) {
                $config += [
                    'foreignKey' => null,
                    'className' => null,
                    'dependent' => false,
                    'fields' => [],
                    'conditions' => [],
                    'association' => $assoc,
                    'order' => null,
                    'propertyName' => $property
                ];

                if ($assoc === 'belongsTo') {
                    unset($config['dependent']);
                }

                $this->validateAssociationDefinition($assoc, $config);
            }
        }
    }

    /**
     * Validates the defintion array has all the correct keys
     *
     * @param string $assoc
     * @param array $config
     * @return void
     */
    protected function validateAssociationDefinition(string $assoc, array $config): void
    {
        if (empty($config['propertyName'])) {
            throw new LogicException(sprintf('%s is missing propertyName', $assoc));
        }

        if (empty($config['foreignKey'])) {
            throw new LogicException(sprintf('%s `%s` is missing foreignKey', $assoc, $config['propertyName']));
        }

        if (empty($config['className'])) {
            throw new LogicException(sprintf('%s `%s` is missing className', $assoc, $config['propertyName']));
        }

        if ($assoc === 'belongsToMany') {
            if (empty($config['joinTable'])) {
                throw new LogicException(sprintf('belongsToMany `%s` is missing joinTable', $config['propertyName']));
            }
            if (empty($config['otherForeignKey'])) {
                throw new LogicException(sprintf('belongsToMany `%s` is missing otherForeignKey', $config['propertyName']));
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
    protected function loadRelatedData(ResultSet $resultSet, QueryObject $query): ResultSet
    {
        $options = $query->getOptions();

        // Preload
        $associations = [];
        foreach ($this->associations as $assoc) {
            foreach ($this->$assoc as $config) {
                $property = $config['propertyName'];
                if (in_array($property, $options['with'])) {
                    $associations[$assoc][$property] = $config;
                }
            }
        }

        $primaryKey = $this->getPrimaryKey()[0];

        foreach ($resultSet as &$entity) {
            $row = $entity->toArray();

            foreach ($associations as $type => $association) {
                foreach ($association as $config) {
                    $conditions = $config['conditions'];
                    $options = ['fields' => $config['fields'], 'order' => $config['order']];

                    $mapper = $this->mapperManager->get($config['className']);
                    $bindingKey = $mapper->getPrimaryKey()[0];

                    switch ($type) {
                            case 'belongsTo':
                                $conditions[$bindingKey] = $row[$config['foreignKey']];
                                $result = $mapper->findAllBy($conditions, $options);
                                $this->setEntityValue($entity, $config['propertyName'], $result ? $result[0] : null);

                            break;
                            case 'hasOne':
                                $conditions[$config['foreignKey']] = $row[$primaryKey];
                                $result = $mapper->findAllBy($conditions, $options);
                                $this->setEntityValue($entity, $config['propertyName'], $result ? $result[0] : null);

                            break;
                            case 'hasMany':
                                $conditions[$config['foreignKey']] = $row[$bindingKey];
                                $this->setEntityValue($entity, $config['propertyName'], $mapper->findAllBy($conditions, $options));

                            break;
                            case 'belongsToMany':
                                $result = $this->dataSource->read(
                                    $config['joinTable'], new QueryObject([$config['foreignKey'] => $row[$primaryKey]])
                                );

                                $otherForeignKey = $config['otherForeignKey'];
                                $ids = array_map(function ($record) use ($otherForeignKey) {
                                    return $record[$otherForeignKey]; // extract tag_id
                                }, $result->toArray());

                                $conditions[$primaryKey] = $ids;
                                $this->setEntityValue($entity, $config['propertyName'], $mapper->findAllBy($conditions, $options));

                            break;
                    }
                }
            }
        }

        return $resultSet;
    }

    private function setEntityValue(EntityInterface $entity, string $property, $value): void
    {
        if ($entity instanceof Entity) {
            $entity->$property = $value;

            return;
        }

        $setter = 'set' . ucfirst($property);
        $entity->$setter($value);
    }

    /**
     * Deletes dependent records for the hasOne, hasMany and belongsToMany associations
     *
     * @param string|integer $id
     * @return void
     */
    private function deleteDependent($id): void
    {
        // User has one profile, user_id in other table
        foreach (['hasOne','hasMany'] as $assoc) {
            foreach ($this->$assoc as $config) {
                if (! empty($config['dependent'])) {
                    $mapper = $this->mapperManager->get($config['className']);
                    foreach ($mapper->findAllBy([$config['foreignKey'] => $id]) as $entity) {
                        $mapper->delete($entity);
                    }
                }
            }
        }

        foreach ($this->belongsToMany as $config) {
            if (! empty($config['dependent'])) {
                $this->dataSource->delete($config['joinTable'], new QueryObject([$config['foreignKey'] => $id]));
            }
        }
    }
}
