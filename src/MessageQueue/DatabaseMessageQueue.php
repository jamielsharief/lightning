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

namespace Lightning\MessageQueue;

use Lightning\MessageQueue\MessageQueueInterface;
use PDO;

class DatabaseMessageQueue implements MessageQueueInterface
{
    private PDO $pdo;
    private string $table;
    private string $driver;

    /**
     * Constructor
     * 
     * @internal some objects such as those with private properties will have null byte characters, e.g.  \x00 this causes
     * for data to be truncated in pgsql, whilst BYTEA would be appropriate its seems messey. There seem to be no problems
     * in Redis, MySQL or Sqlite or even PHP since these are strings. So data for postgres will need to be encoded.
     */
    public function __construct(PDO $pdo, string $table)
    {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

  

    /**
     * Sends a message to the message queue
     */
    public function send(string $queue, string $message, int $delay = 0): bool
    {
        $statement = $this->pdo->prepare("INSERT INTO {$this->table} (body,queue,scheduled) VALUES (:body,:queue,:scheduled)");

        return $statement->execute([
            'body' => $message,
            'queue' => $queue,
            'scheduled' => date('Y-m-d H:i:s', time() + $delay)
        ]);
    }

    /**
     * Receives the next message from the queue, if any
     */
    public function receive(string $queue): ?string
    {
        $result = null;

        $this->driver === 'sqlite' ? $this->query('BEGIN EXCLUSIVE TRANSACTION') : $this->pdo->beginTransaction();

        $row = $this->query("SELECT * FROM {$this->table} WHERE {$this->table}.scheduled <= :scheduled LIMIT 1", [
            'scheduled' => date('Y-m-d H:i:s')
        ]);

        if ($row) {
            $id = $row['id'];

            if ($this->execute("DELETE FROM {$this->table} WHERE {$this->table}.id = :id", ['id' => $id])) {
                $result = $row['body'];
            }
        }

        $this->driver === 'sqlite' ? $this->query('COMMIT') : $this->pdo->commit();

        return $result;
    }

    /**
     * Executes a query
     */
    private function execute(string $sql, array $params = []): bool
    {
        $statement = $this->pdo->prepare($sql);

        return $statement ? $statement->execute($params) : false;
    }

    /**
     * Executes a query and fetches a single row
     */
    private function query(string $sql, array $params = []): ?array
    {
        $result = null;

        $statement = $this->pdo->prepare($sql);
        if ($statement && $statement->execute($params)) {
            $result = $statement->fetch(PDO::FETCH_ASSOC) ?: null;
        }

        return $result;
    }
}
