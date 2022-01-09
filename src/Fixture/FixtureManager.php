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

namespace Lightning\Fixture;

use PDO;
use Exception;

/**
 * Simple Fixture Manager
 */
class FixtureManager
{
    private PDO $pdo;
    private SqlDialectInterface $sqlDialect;

    /**
     * @var \Lightning\Fixture\AbstractFixture[] $fixtures
     */
    protected array $fixtures = [];

    /**
     * Constructor
     *
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->sqlDialect = (new SqlDialectFactory())->create($pdo->getAttribute(PDO::ATTR_DRIVER_NAME));
    }

    /**
     * Loads Fixtures
     *
     * @param array $fixtures
     * @return void
     */
    public function load(array $fixtures): void
    {
        foreach ($fixtures as $fixture) {
            $this->loadFixture(new $fixture());
        }
    }

    /**
     * Loads a Fixture
     *
     * @param AbstractFixture $fixture
     * @return void
     */
    protected function loadFixture(AbstractFixture $fixture): void
    {
        array_push($this->fixtures, $fixture);

        $this->disableForeignKeyConstraints();

        $this->truncate($fixture->getTable());
        $this->insertRecords($fixture->getTable(), $fixture->getRecords());

        $this->enableForeignKeyConstraints();
    }

    /**
    * Disables Foreign key checks
    *
    * @return void
    */
    private function disableForeignKeyConstraints(): void
    {
        $this->executeStatements(
            $this->sqlDialect->disableForeignKeyConstraints()
        );
    }

    /**
     * Enables Foreign key checks
     *
     * @return void
     */
    private function enableForeignKeyConstraints(): void
    {
        $this->executeStatements(
            $this->sqlDialect->enableForeignKeyConstraints()
        );
    }

    /**
     * Truncates the DB
     *
     * @param string $table
     * @return void
     */
    private function truncate(string $table): void
    {
        $this->executeStatements($this->sqlDialect->truncate($table));
    }

    /**
     * Executes multiple statements
     *
     * @param array $statements
     * @return void
     */
    private function executeStatements(array $statements): void
    {
        foreach ($statements as $statement) {
            $this->pdo->exec($statement);
        }
    }

    /**
     * Unloads the fixtures
     *
     * @return void
     */
    public function unload(): void
    {
        foreach ($this->fixtures as $fixture) {
            $this->unloadFixture($fixture);
        }
    }

    /**
     * Unload the Fixtures
     *
     * @param AbstractFixture $fixture
     * @return void
     */
    protected function unloadFixture(AbstractFixture $fixture): void
    {
        $this->disableForeignKeyConstraints();
        $this->truncate($fixture->getTable());
        $this->enableForeignKeyConstraints();
    }

    /**
     * Insert the records into the DB
     *
     * @param string $table
     * @param array $records
     * @return void
     */
    protected function insertRecords(string $table, array $records): void
    {
        foreach ($records as $record) {
            $this->insertRecord($table, $record);
        }
    }

    /**
     * Inserts a record into the db
     *
     * @param string $table
     * @param array $record
     * @return void
     */
    protected function insertRecord(string $table, array $record): void
    {
        $fields = array_keys($record);
        $questionMarks = array_fill(0, count($fields), '?');

        array_walk($fields, function (&$value) {
            $value = $this->sqlDialect->quoteIdentifier($value);
        });

        $fields = implode(', ', $fields);
        $values = implode(', ', $questionMarks);

        $statement = $this->pdo->prepare("INSERT INTO {$this->sqlDialect->quoteIdentifier($table)} ({$fields}) VALUES ({$values})");

        if (! $statement->execute(array_values($record))) {
            throw new Exception("Error inserting records into table `{$table}`");
        }
    }
}
