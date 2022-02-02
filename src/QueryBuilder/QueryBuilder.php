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

namespace Lightning\QueryBuilder;

use Stringable;
use RuntimeException;
use BadMethodCallException;

/**
 * SQL Builder
 */
class QueryBuilder implements Stringable
{
    protected string $type = 'select';

    protected ?string $quote;

    protected array $parts = [
        'select' => [],
        'distinct' => false,
        'from' => null,
        'joins' => [],
        'where' => [],
        'groupBy' => [],
        'having' => null,
        'orderBy' => [],
        'limit' => [],
        'insert' => [],
        'values' => [],
        'set' => []

    ];

    private ?string $table = null;
    private ?string $tableAlias = null;

    /**
     * Parameter counter
     *
     * @var integer
     */
    protected int $counter = 0;
    protected $values = [];

    /**
     * Constructor
     *
     * @param string|null $quote
     */
    public function __construct(?string $quote = null)
    {
        $this->quote = $quote;
    }

    /**
     * Gets the query type
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Creates an Insert query
     *
     * @param array $columns
     * @return self
     */
    public function insert(array $columns): self
    {
        $this->reset('insert');
        $this->parts['insert'] = $columns;

        return $this;
    }

    /**
     * Sets the table name for the Insert query
     *
     * @param string $table
     * @return self
     */
    public function into(string $table): self
    {
        $this->table = $table;
        $this->tableAlias = $table;

        return $this;
    }

    /**
     * Sets the values for the insert query
     *
     * @param array $values
     * @return self
     */
    public function values(array $values): self
    {
        $this->parts['values'] = $values;

        return $this;
    }

    /**
     * Sets the columns to be to returned
     *
     * @param array $columns
     * @return self
     */
    public function select(array $columns): self
    {
        $this->reset('select');
        $this->parts['select'] = $columns;

        return $this;
    }

    /**
     * Sets the table name
     *
     * @param string $table
     * @param string|null $alias
     * @return self
     */
    public function from(string $table, ?string $alias = null): self
    {
        $this->table = $table;
        $this->tableAlias = $alias ?: $table;

        $this->parts['from'] = $alias ? $this->quote($table) . ' AS '  . $this->quote($alias) : $this->quote($table);

        return $this;
    }

    /**
     * @param string $table e.g. customers
     * @param string|null $alias
     * @param string|array $conditions orders.customer_id = customers.id
     * @return self
     */
    public function leftJoin(string $table, ?string $alias = null, $conditions = []): self
    {
        $this->parts['joins'][] = ['type' => 'LEFT','table' => $table,'alias' => $alias,'conditions' => (array) $conditions];

        return $this;
    }

    /**
     * @param string $table e.g. customers
     * @param string|null $alias
     * @param string|array $conditions orders.customer_id = customers.id
     * @return self
     */
    public function rightJoin(string $table, ?string $alias = null, $conditions = []): self
    {
        $this->parts['joins'][] = ['type' => 'RIGHT','table' => $table,'alias' => $alias,'conditions' => (array) $conditions];

        return $this;
    }

    /**
     * @param string $table e.g. customers
     * @param string|null $alias
     * @param string|array $conditions orders.customer_id = customers.id
     * @return self
     */
    public function innerJoin(string $table, ?string $alias = null, $conditions = []): self
    {
        $this->parts['joins'][] = ['type' => 'INNER','table' => $table,'alias' => $alias,'conditions' => (array) $conditions];

        return $this;
    }

    /**
     * @param string $table e.g. customers
     * @param string|null $alias
     * @param string|array $conditions orders.customer_id = customers.id
     * @return self
     */
    public function fullJoin(string $table, ?string $alias = null, $conditions = []): self
    {
        $this->parts['joins'][] = ['type' => 'FULL','table' => $table,'alias' => $alias,'conditions' => (array) $conditions];

        return $this;
    }

    /**
    * Creates a set of where conditions, overwriting previous.
    *
    * @param array $conditions
    * @return self
    */
    public function where(array $conditions): self
    {
        $this->parts['where'] = [];

        return $this->and($conditions);
    }

    /**
     * Adds a condition or conditions after an OR
     *
     * @param array $conditions
     * @return self
     */
    public function or(array $conditions): self
    {
        $this->parts['where'][] = ['operator' => 'OR', 'conditions' => $conditions];

        return $this;
    }

    /**
     * Adds a condition or conditions after an OR
     *
     * @param array $conditions
     * @return self
     */
    public function and(array $conditions): self
    {
        $this->parts['where'][] = ['operator' => 'AND', 'conditions' => $conditions];

        return $this;
    }

    /**
     * Converts array based conditions into SQL conditions
     *
     * @param array $conditions
     * @return array
     */
    private function whereConditions(array $conditions): array
    {
        $result = [];

        foreach ($conditions as $field => $value) {
            if (is_int($field)) {
                $result[] = $value;

                continue;
            }

            /**
             * Handle nesting conditions e.g with OR.
             * 1. if its a single condition then it would be A = ? OR A = ?
             * 2. if its multiple conditions then it would be A = ? OR (A = ? AND B = ?)
             */
            if (in_array($field, ['AND','OR','NOT'])) {
                $nestedConditions = $this->whereConditions($value);
                $template = count($nestedConditions) > 1 ? '%s (%s)' : '%s %s';
                $result[] = sprintf($template, $field, implode(' AND ', $nestedConditions));

                continue;
            }

            if (strpos($field, ' ') === false) {
                $expression = '=';
            } else {
                list($field, $expression) = explode(' ', $field, 2); //['id !=' => 1]
            }

            $result[] = $this->createCondition($this->prepareColumn($field), $expression, $value);
        }

        return $result;
    }

    /**
     * Sets the group by clause
     *
     * @param string|array $group c1 or ['c1','c2']
     * @return self
     */
    public function groupBy($group): self
    {
        $this->parts['groupBy'] = (array) $group;

        return $this;
    }

    /**
     * Sets the having clause
     *
     * @param string|array $conditions
     * @return self
     */
    public function having($conditions): self
    {
        $this->parts['having'] = (array) $conditions;

        return $this;
    }

    /**
     * Sets the order by clause
     *
     * @param string|array $sort  authors.country ASC, 'country' => 'ASC',['country, name ASC'],'country
     * @return self
     */
    public function orderBy($sort): self
    {
        $this->parts['orderBy'] = (array) $sort;

        return $this;
    }

    /**
     * Sets the limit clause
     *
     * @param integer $limit
     * @param integer|null $offset
     * @return self
     */
    public function limit(int $limit, ?int $offset = null): self
    {
        $this->parts['limit'] = [$limit,$offset];

        return $this;
    }

    /**
     * Creates an Update query
     *
     * @param string $table
     * @return self
     */
    public function update(string $table): self
    {
        $this->reset('update');
        $this->from($table);

        return $this;
    }

    /**
     * Sets the field or fields and their values for an Update query
     *
     * @param array $data
     * @return self
     */
    public function set(array $data): self
    {
        $this->parts['set'] = $data;

        return $this;
    }

    /**
     * Creates a Delete query
     *
     * @return self
     */
    public function delete(): self
    {
        $this->reset('delete');

        return $this;
    }

    /**
     * Creates the SQL clause
     *
     * @param string $field
     * @param string $expression
     * @param mixed $value
     * @return string
     */
    private function createCondition(string $field, string $expression, $value): string
    {
        if (! in_array($expression, ['BETWEEN','NOT BETWEEN','=', '!=', '<','>','<=','<>','>=','IN', 'NOT IN','LIKE','NOT LIKE'])) {
            throw new BadMethodCallException(sprintf('Invalid expression `%s`', $expression));
        }

        $isArray = is_array($value);
        $valueIsEmpty = $value === null || ($isArray && empty($value));

        // Not equals <> follows ISO standard, != does not
        switch ($expression) {
            case '=':
                if ($valueIsEmpty) {
                    return $field . ' IS NULL';
                } elseif ($isArray) {
                    return $field . ' IN ' . $this->arrayToPlaceHoldersString($value);
                }

                return $field . ' = ' . $this->getPlaceholder($value); // ISO Standard

            case '!=':
            case '<>':
                if ($valueIsEmpty) {
                    return $field . ' IS NOT NULL';
                } elseif ($isArray) {
                    return $field . ' NOT IN ' . $this->arrayToPlaceHoldersString($value);
                }

                return $field . ' <> ' . $this->getPlaceholder($value); // ISO Standard
            case '<':
            case '<=':
            case '>=':
            case '>':
            case 'LIKE':
            case 'NOT LIKE':

                if (! $isArray) {
                    return $field . ' ' . $expression . ' ' . $this->getPlaceholder($value);
                }

                break;
            case 'BETWEEN':
            case 'NOT BETWEEN':

                if ($isArray && count($value) === 2) {
                    $p1 = $this->getPlaceholder($value[0]);
                    $p2 = $this->getPlaceholder($value[1]);

                    return sprintf('%s %s %s AND %s', $field, $expression, $p1, $p2);
                }

                break;
            case 'IN':
            case 'NOT IN':
                if ($isArray) {
                    return $field . ' ' . $expression . ' ' . $this->arrayToPlaceHoldersString($value);
                }

            break;
        }

        throw new RuntimeException('Error parsing expression');
    }

    /**
     * Binds a paramater to the query
     *
     * @param int|string $key If you are using named placeholders, e.g. ':email' then pass 'email' if you are using question mark
     *                        placeholders, then the key will be an integer starting at 0 for the first item.
     * @param mixed $value
     * @return self
     */
    public function setParameter($key, $value): self
    {
        $key = is_int($key) ? $key : ':' . $key; // set name to :name
        $this->values[$key] = $value;

        return $this;
    }

    /**
     * Sets an array of parameters (named or question mark placeholders)
     *
     * @param array $parameters For question mark placeholders [0 => 'jon', 1 =>'smith'] or for named placholders e.g. :last_name, then use
     *              ['first_name' => 'jon' ,'last_name' => 'smith' ]
     * @return self
     */
    public function setParameters(array $parameters): self
    {
        array_walk($parameters, function ($value, $key) {
            $this->setParameter($key, $value);
        });

        return $this;
    }

    /**
     * Get an array of parameterized values (must call getSql first)
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->values;
    }

    /**
     * Gets this query as SQL
     *
     * @return string
     */
    public function toString(): string
    {
        // Reset this before compiling
        $this->counter = 0;
        $this->values = [];

        switch ($this->type) {
            case 'select':
                $sql = $this->compileSelect();

            break;
            case 'insert':
                $sql = $this->compileInsert();

            break;
            case 'update':
                $sql = $this->compileUpdate();

            break;
            case 'delete':
                $sql = $this->compileDelete();

            break;
        }

        return $sql;
    }

    /**
     * Compiles the Insert query
     *
     * @return string
     */
    private function compileInsert(): string
    {
        if (empty($this->table)) {
            throw new BadMethodCallException('Table for the query was not set');
        }

        $params = [];
        foreach ($this->parts['values'] as $value) {
            $params[] = $this->getPlaceholder($value);
        }

        $columns = [];
        foreach ($this->parts['insert'] as $column) {
            $columns[] = $this->quote($column);
        }

        return sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->quote($this->table),
            implode(', ', $columns),
            implode(', ', $params)
        );
    }

    /**
     * Compiles the Select query
     *
     * @return string
     */
    private function compileSelect(): string
    {
        if (empty($this->parts['from'])) {
            throw new BadMethodCallException('Table for the query was not set');
        }

        $statement = [
            sprintf(
                'SELECT %s FROM %s',
                implode(', ', $this->prepareColumns($this->parts['select'])),
                $this->parts['from']
            )
        ];

        foreach ($this->parts['joins'] as $join) {
            $table = $this->quote($join['table']);
            if ($join['alias']) {
                $table .= ' AS ' . $join['alias'];
            }
            $statement[] = sprintf('%s JOIN %s ON %s', $join['type'], $table, implode(' AND ', $this->whereConditions($join['conditions'])));
        }

        if ($this->parts['where']) {
            $statement[] = sprintf('WHERE %s', $this->compileConditions($this->parts['where']));
        }

        if ($this->parts['groupBy']) {
            $statement[] = sprintf('GROUP BY %s', implode(', ', $this->prepareColumns($this->parts['groupBy'])));
        }

        if ($this->parts['having']) {
            $statement[] = sprintf('HAVING %s', implode(' AND ', $this->whereConditions($this->parts['having'])));
        }

        if ($this->parts['orderBy']) {
            $statement[] = sprintf('ORDER BY %s', implode(', ', $this->processOrderBy($this->parts['orderBy'])));
        }

        if ($this->parts['limit']) {
            $statement[] = sprintf('LIMIT %d', $this->parts['limit'][0]);

            if ($this->parts['limit'][1]) {
                $statement[] = sprintf('OFFSET %d', $this->parts['limit'][1]);
            }
        }

        return implode(' ', $statement);
    }

    private function compileConditions(array $where): string
    {
        $result = '';

        $multipleWhere = count($where) > 1;

        foreach ($where as $conditionGroup) {
            $conditions = $this->whereConditions($conditionGroup['conditions']);

            $start = $end = '';
            if ($multipleWhere && count($conditions) > 1) {
                $start = '(';
                $end = ')';
            }

            $result .= sprintf(' %s %s%s%s', $conditionGroup['operator'], $start, implode(' AND ', $conditions), $end);
        }

        // clean up groups, nested ANDS/OR - ignore NOT
        return preg_replace(['/^( AND | OR )/','/AND OR /','/AND AND /'], ['','OR ','AND '], $result);
    }

    /**
     * Compiles the Update query
     *
     * @return string
     */
    private function compileUpdate(): string
    {
        $sets = [];
        foreach ($this->parts['set'] as $key => $value) {
            $sets[] = $this->quote($key) . ' = ' .   $this->getPlaceholder($value);
        }

        $statement = [
            sprintf('UPDATE %s SET %s', $this->quote($this->table), implode(', ', $sets))
        ];

        if ($this->parts['where']) {
            $statement[] = sprintf('WHERE %s', $this->compileConditions($this->parts['where']));
        }

        return implode(' ', $statement);
    }

    /**
     * Compiles the Delete query
     *
     * @return string
     */
    private function compileDelete(): string
    {
        if (empty($this->table)) {
            throw new BadMethodCallException('Table for the query was not set');
        }

        $statement = [sprintf('DELETE FROM %s', $this->quote($this->table))];

        if ($this->parts['where']) {
            $statement[] = sprintf('WHERE %s', $this->compileConditions($this->parts['where']));
        }

        return implode(' ', $statement);
    }

    /**
     * Magic method to be called when converting this object to a string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Resets the state of the query
     *
     * @param string $type
     * @return void
     */
    private function reset(string $type): void
    {
        $this->type = $type;

        $this->parts = [
            'select' => [],
            'distinct' => false,
            'from' => null,
            'joins' => [],
            'where' => [],
            'groupBy' => [],
            'having' => null,
            'orderBy' => [],
            'limit' => [],
            'insert' => [],
            'values' => [],
            'set' => []
        ];
        $this->table = $this->tableAlias = null;

        $this->counter = 0;
        $this->values = [];
    }

    /**
     * Quote and add table aliases to values in the order, and ensure that ASC/DESC are capitalized
     *
     * @param array $data
     * @return array
     */
    private function processOrderBy(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            if (is_string($key)) {
                $value = $this->prepareColumn($key) . ' ' . strtoupper($value); // ['surname' => 'ASC']
            } elseif (strpos($value, ' ') === false) {
                $value = $this->prepareColumn($value); // e.g. 'surname'
            } else {
                list($column, $order) = explode(' ', $value, 2); // e.g 'surname DESC'
                $order = strtoupper($order);
                if (in_array($order, ['ASC','DESC'])) {
                    $value = $this->prepareColumn($column) . ' ' . $order;
                }
            }
            $result[] = $value;
        }

        return $result;
    }

    /**
     * @param array $data
     * @return string
     */
    private function arrayToPlaceHoldersString(array $data): string
    {
        $out = array_map(function ($value) {
            return $this->getPlaceholder($value);
        }, $data);

        return  '( ' . implode(', ', $out) . ' )';
    }

    /**
     * @param mixed $value
     * @return string
     */
    private function getPlaceholder($value): string
    {
        $placeholder = ':v' . $this->counter;
        $this->values[$placeholder] = $value;
        $this->counter++;

        return $placeholder;
    }

    /**
    * This will quote any item, no checking
    *
    * @param string $item
    * @return string
    */
    private function quote(string $item): string
    {
        return  $this->quote . $item . $this->quote;
    }

    /**
     * Only quote columns, not formulas or AS etc
     *
     * @param string $column
     * @return string
     */
    private function prepareColumn(string $column): string
    {
        if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $column)) {
            $column = $this->quote . $this->tableAlias . $this->quote . '.' . $this->quote . $column . $this->quote;
        }

        return $column;
    }

    private function prepareColumns(array $columns): array
    {
        return array_map(function ($column) {
            return $this->prepareColumn($column);
        }, $columns);
    }
}
