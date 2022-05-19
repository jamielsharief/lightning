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

use PDO;
use PDOStatement;
use RuntimeException;
use Lightning\Database\Row;
use InvalidArgumentException;
use Lightning\DataMapper\QueryObject;
use Lightning\QueryBuilder\QueryBuilder;
use Lightning\DataMapper\DataSourceInterface;

class DatabaseDataSource implements DataSourceInterface
{
    protected PDO $pdo;
    protected QueryBuilder $builder;

    /**
     * Mixed
     *
     * @var string|int|null
     */
    protected $id = null;

    /**
     * Constructor
    */
    public function __construct(PDO $pdo, QueryBuilder $builder)
    {
        $this->pdo = $pdo;
        $this->builder = $builder;
    }

    /**
     * @return string|int|null
     */
    public function getGeneratedId()
    {
        return $this->id;
    }

    /**
     * Creates a record in the database
     */
    public function create(string $table, array $data): bool
    {
        $builder = $this->builder
            ->insert(array_keys($data))
            ->into($table)
            ->values(array_values($data));

        $result = $this->execute($builder->toString(), $builder->getParameters())->rowCount() === 1;

        if ($result) {
            $id = $this->pdo->lastInsertId(); // Don't pass table Wrong object type: 7 ERROR:  "articles" is not a sequence
            $this->id = ctype_digit($id) ? (int) $id : $id;
        }

        return $result;
    }

    /**
     * Reads from the DataSource
     */
    public function read(string $table, QueryObject $query): array
    {
        $criteria = $query->getCriteria();
        $options = $query->getOptions();

        $builder = $this->builder
            ->select(empty($options['fields']) ? ['*'] : $options['fields'])
            ->from($table);
        if ($criteria) {
            $builder->where($criteria);
        }
        $this->applyOptions($builder, $options);

        return $this->execute($builder->toString(), $builder->getParameters())->fetchAll(PDO::FETCH_CLASS, Row::class);
    }

    /**
     * Updates records in the datasource
     */
    public function update(string $table, QueryObject $query, array $data): int
    {
        $criteria = $query->getCriteria();

        $builder = $this->builder->update($table)->set($data);
        if ($criteria) {
            $builder->where($criteria);
        }
        $this->applyOptions($builder, $query->getOptions());

        return $this->execute($builder->toString(), $builder->getParameters())->rowCount();
    }

    /**
     * Deletes records from the Datasource
     */
    public function delete(string $table, QueryObject $query): int
    {
        $criteria = $query->getCriteria();

        $builder = $this->builder->delete()->from($table);
        if ($criteria) {
            $builder->where($criteria);
        }
        $this->applyOptions($builder, $query->getOptions());

        return $this->execute($builder->toString(), $builder->getParameters())->rowCount();
    }

    public function count(string $table, QueryObject $query): int
    {
        $criteria = $query->getCriteria();
        $fields = array_merge(['COUNT(*) as count'], $query->getOption('group', []));

        $builder = $this->builder->select($fields)->from($table);
        if ($criteria) {
            $builder->where($criteria);
        }
        $this->applyOptions($builder, $query->getOptions());

        return (int) $this->execute($builder->toString(), $builder->getParameters())->fetchColumn(0);
    }

    /**
    * Runs a SELECT query on the database
    */
    public function query(string $sql, array $params = [], int $mode = PDO::FETCH_ASSOC): array
    {
        return $this->execute($sql, $params)->fetchAll($mode);
    }

    /**
     * Execute raw queries on the data source
     */
    public function execute(string $sql, array $params = []): PDOStatement
    {
        $statement = $this->pdo->prepare($sql);
        if ($statement->execute($params)) {
            return $statement;
        }

        throw new RuntimeException($statement->errorInfo()[2] ?? sprintf('ERROR Executing: %s', $sql));
    }

    private function applyOptions(QueryBuilder $builder, array $options): void
    {
        $joins = $options['joins'] ?? [];

        foreach ($joins as $join) {
            if (! isset($join['table'])) {
                throw new InvalidArgumentException(sprintf('Join configuration array is missing `table`'));
            }
            $type = strtolower($join['type'] ?? 'LEFT');

            if (! in_array($type, ['left','right','full','inner'])) {
                throw new InvalidArgumentException(sprintf('Invalid join type `%s`', $type));
            }
            $method = $type . 'Join';
            $builder->$method($join['table'], $join['alias'] ?? null, $join['conditions'] ?? []);
        }

        if (! empty($options['group'])) {
            $builder->groupBy($options['group']);
        }

        if (! empty($options['having'])) {
            $builder->having($options['having']);
        }

        if (! empty($options['order'])) {
            $builder->orderBy($options['order']);
        }

        if (! empty($options['limit'])) {
            $builder->limit($options['limit'], $options['offset'] ?? null);
        }
    }
}
