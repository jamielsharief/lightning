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
      *       'class' => Profile::class
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
     *       'class' => User::class
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
     *      'class' => User::class
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
     *      'class' => User::class
     *      'joinTable' => 'tags_users',
     *      'foreignKey' => 'tag_id',
     *      'associatedForeignKey' => 'user_id', // the foreignKey for the associated model
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
                $config += ['foreignKey' => null,'class' => null, 'dependent' => false, 'alias' => null, 'fields' => [], 'conditions' => [],'association' => $assoc, 'order' => null];

                if ($assoc === 'belongsTo') {
                    unset($config['dependent']);
                }

                if (empty($config['foreignKey'])) {
                    throw new LogicException(sprintf('%s `%s` is missing foreignKey', $property, $assoc));
                }

                if (empty($config['class'])) {
                    throw new LogicException(sprintf('%s `%s` is missing class', $property, $assoc));
                }

                if ($assoc === 'belongsToMany') {
                    if (empty($config['joinTable'])) {
                        throw new LogicException(sprintf('belongsToMany `%s` requires a joinTable key', $property));
                    }
                    if (empty($config['associatedForeignKey'])) {
                        throw new LogicException(sprintf('belongsToMany `%s` is missing associatedForeignKey', $property));
                    }
                }
            }
        }
    }

    private function setEntityValue(EntityInterface $entity, string $property, $value): void
    {
        if ($entity instanceof Entity) {
            $entity[$property] = $value;
        } else {
            $setter = 'set' . ucfirst($property);
            $entity->$setter($value);
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

        // Preload associations for performance
        if (! isset($options['with'])) {
            return $resultSet;
        }

        foreach ($this->associations as $assoc) {
            foreach ($this->$assoc as $property => $config) {
                if (in_array($property, $options['with'])) {
                    $associations[$assoc][$property] = $config;
                }
            }
        }

        foreach ($resultSet as &$entity) {
            $row = $entity->toArray();
            foreach ($associations as $type => $association) {
                foreach ($association as $property => $config) {
                    $mapper = $this->mapperManager->get($config['class']);

                    switch ($type) {
                            case 'belongsTo':
                                $primaryKey = $mapper->getPrimaryKey()[0];

                                $result = $mapper->findAllBy([
                                    $primaryKey => $row[$config['foreignKey']]
                                ]);

                                $this->setEntityValue($entity, $property, $result ? $result[0] : null);

                            break;
                            case 'hasOne':
                                $primaryKey = $this->getPrimaryKey()[0];

                                $result = $mapper->findAllBy([
                                    $config['foreignKey'] => $row[$primaryKey]
                                ]);

                                $this->setEntityValue($entity, $property, $result ? $result[0] : null);

                            break;
                            case 'hasMany':
                                $primaryKey = $mapper->getPrimaryKey()[0];

                                $result = $mapper->findAllBy(
                                    [$config['foreignKey'] => $row[$primaryKey]]
                                );

                                $this->setEntityValue($entity, $property, $result);

                            break;
                            case 'belongsToMany':
                                $primaryKey = $this->getPrimaryKey()[0];
                                $associationForeignKey = $config['associatedForeignKey'];

                                $result = $this->dataSource->read(
                                    $config['joinTable'], new QueryObject([$config['foreignKey'] => $row[$primaryKey]])
                                );

                                $ids = array_map(function ($record) use ($associationForeignKey) {
                                    return $record[$associationForeignKey]; // extract tag_id
                                }, $result->toArray());

                                $result = $mapper->findAllBy([
                                    $primaryKey => $ids
                                ]);

                                $this->setEntityValue($entity, $property, $result);

                            break;

                    }
                }
            }
        }

        return $resultSet;
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
                    $mapper = $this->mapperManager->get($config['class']);
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
