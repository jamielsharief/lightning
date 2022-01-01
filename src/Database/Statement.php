<?php declare(strict_types=1);
/**
 * LightningPHP
 * Copyright 2021 Jamiel Sharief.
 *
 * Licensed under The MIT License
 * The above copyright notice and this permission notice shall be included in all copies or substantial
 * portions of the Software.
 *
 * @copyright   Copyright (c) Jamiel Sharief
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */

namespace Lightning\Database;

use PDO;
use Countable;
use Stringable;
use Traversable;
use PDOStatement;
use ArrayIterator;
use IteratorAggregate;

class Statement implements Countable, Stringable, IteratorAggregate
{
    private PDOStatement $statement;
    private bool $executed = false;

    /**
     * Constructor
     *
     * @param PDOStatement $statement
     * @param string $sql
     */
    public function __construct(PDOStatement $statement)
    {
        $this->statement = $statement;
    }

    /**
     * Binds an array of values with types if provided. If using positional palceholders
     * there is no need to set a key.
     *
     * Using bindValue is prefered see https://phpdelusions.net/pdo#methods
     *
     * @param array $params
     * @param array $types
     * @return void
     */
    public function bind(array $params, array $types = []): void
    {
        if (empty($params)) {
            return;
        }

        $questionMarks = is_int(key($params)); // performance do here

        foreach ($params as $index => $value) {
            $type = $types[$index] ?? PDO::PARAM_STR;
            if ($questionMarks) {
                $index += 1; // Columns/Parameters are 1-based
            }

            $this->bindValue($index, $value, $type);
        }
    }

    /**
     * Binds a value to positional value or named placeholder
     *
     * @param string|integer $param
     * @param mixed $value
     * @param int $type
     * @return string
     */
    public function bindValue($param, $value, int $type = PDO::PARAM_STR): bool
    {
        return $this->statement->bindValue($param, $value, $type);
    }

    /**
     * Gets the last error code when this statement was executed
     *
     * @return array
     */
    public function errorInfo(): array
    {
        return $this->statement->errorInfo();
    }

    /**
     * Gets the last error code when this statement was executed
     *
     * @return string|null
     */
    public function errorCode(): ?string
    {
        return $this->statement->errorCode();
    }

    /**
     * Executes a statement
     *
     * @param array|null $params
     * @return boolean
     */
    public function execute(?array $params = null): bool
    {
        $this->executed = true;

        return $this->statement->execute($params);
    }

    /**
     * Gets the SQL statement
     *
     * @return string
     */
    public function getQueryString(): string
    {
        return  $this->statement->queryString;
    }

    /**
     * Gets the row count
     *
     * @return integer
     */
    public function rowCount(): int
    {
        return $this->statement->rowCount();
    }

    /**
     * Gets the row count (Countable interface)
     *
     * @return integer
     */
    public function count(): int
    {
        return $this->statement->rowCount();
    }

    /**
     * Gets the row count
     *
     * @return integer
     */
    public function columnCount(): int
    {
        return $this->statement->columnCount();
    }

    /**
     * Closes the cusor so the statement can be executed again
     *
     * @see https://www.php.net/manual/en/pdostatement.closecursor.php
     * @return boolean
     */
    public function closeCursor(): bool
    {
        return $this->statement->closeCursor();
    }

    /**
     * Gets the iterator
     *
     * TODO: in PHP 8.0 there is getIterator
     *
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        if (! $this->executed) {
            $this->execute();
        }

        return new ArrayIterator($this->fetchAll() ?: []);
    }

    /**
     * Fetches the next row indexed by column number as returned in your result set, starting at column 0
     *
     * @param integer $mode
     * @return mixed
     */
    public function fetch(int $mode = 0)
    {
        return $this->statement->fetch(...func_get_args());
    }

    /**
     * Fetches the next row as an associatve array
     *
     * @return array|false
     */
    public function fetchAssociative()
    {
        return $this->statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
    * Fetches the next row as an associatve array
    *
    * @return array<int, mixed>|false
    */
    public function fetchNumeric()
    {
        return $this->statement->fetch(PDO::FETCH_NUM);
    }

    /**
     * Fetches the next row as an associatve array
     *
     * @param string|null $class
     * @return mixed
     */
    public function fetchObject(?string $class = null)
    {
        return $this->statement->fetchObject($class);
    }

    /**
     * Fetches all records that match, an empty array is returned if there are no results but
     * false on failure
     *
     * @param int $mode
     * @return array|false
     */
    public function fetchAll(int $mode = 0)
    {
        return $this->statement->fetchAll(...func_get_args());
    }

    /**
     * Fetches all records that match as associtative arrays, empty array for no results false on failure
     *
     * @return array<string, mixed>|false
     */
    public function fetchAllAssociative()
    {
        return $this->statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetches all records that match as objects, empty array for no results false on failure
     *
     * @param string $class
     * @return array[object]|false
     */
    public function fetchAllObject(?string $class = null)
    {
        return $class ? $this->statement->fetchAll(PDO::FETCH_CLASS, $class) : $this->statement->fetchAll(PDO::FETCH_OBJ) ;
    }

    /**
     * Fetches all records that match using column numbers, empty array for no results false on failure
     *
     * @return array<int, mixed>|false
     */
    public function fetchAllNumeric()
    {
        return $this->statement->fetchAll(PDO::FETCH_NUM);
    }

    /**
     * Sets the default fetch mode for this statement
     *
     * @internal tricky function can throw General error: fetch mode doesn't allow any extra arguments if you
     * pass null when no arguments are required.
     * @see https://www.php.net/manual/en/pdostatement.setfetchmode.php
     *
     * @param integer $mode
     * @return boolean
     */
    public function setFetchMode(int $mode): bool
    {
        return $this->statement->setFetchMode(...func_get_args());
    }

    /**
     * Fetches a single column, this is a helper function when your query is just
     * selecting a single column then this will extract the data for you.
     *
     * @param integer $column
     * @return mixed
     */
    public function fetchColumn(int $column = 0)
    {
        $result = $this->fetchNumeric();
        if ($result && isset($result[$column])) {
            return $result[$column];
        }

        return false;
    }

    /**
     * Gets the statement as string with values interpolated
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->statement->queryString;
    }
}
