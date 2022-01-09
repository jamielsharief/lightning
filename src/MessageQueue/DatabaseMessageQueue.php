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

namespace Lightning\MessageQueue;

use PDO;

class DatabaseMessageQueue extends AbstractMessageQueue implements MessageQueueInterface
{
    private PDO $pdo;
    private string $table;
    private string $driver;

    /**
     * Constructor
     *
     * @param PDO $pdo
     * @param string $table
     */
    public function __construct(PDO $pdo, string $table)
    {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    /**
     * Sends a message to the message queue
     *
     * @param string $queue
     * @param object $message
     * @param integer $delay
     * @return boolean
     */
    public function send(string $queue, object $message, int $delay = 0): bool
    {
        $statement = $this->pdo->prepare("INSERT INTO {$this->table} (body,queue,scheduled) VALUES (:body,:queue,:scheduled)");

        return $statement->execute([
            'body' => $this->serialize($message),
            'queue' => $queue,
            'scheduled' => date('Y-m-d H:i:s', time() + $delay)
        ]);
    }

    /**
     * Receives the next message from the queue, if any
     *
     * @param string $queue
     * @return object|null
     */
    public function receive(string $queue): ?object
    {
        $result = null;

        $this->driver === 'sqlite' ? $this->query('BEGIN EXCLUSIVE TRANSACTION') : $this->pdo->beginTransaction();

        $row = $this->query("SELECT * FROM {$this->table} WHERE {$this->table}.scheduled <= :scheduled LIMIT 1", [
            'scheduled' => date('Y-m-d H:i:s')
        ]);

        if ($row) {
            $id = $row['id'];

            if ($this->execute("DELETE FROM {$this->table} WHERE {$this->table}.id = :id", ['id' => $id])) {
                $result = $this->unserialize($row['body']);
            }
        }

        $this->driver === 'sqlite' ? $this->query('COMMIT') : $this->pdo->commit();

        return $result;
    }

    private function execute(string $sql, array $params = []): bool
    {
        $statement = $this->pdo->prepare($sql);

        return $statement ? $statement->execute($params) : false;
    }

    private function query(string $sql, array $params = []): ?array
    {
        $statement = $this->pdo->prepare($sql);
        if ($statement && $statement->execute($params)) {
            return $statement->fetch(PDO::FETCH_ASSOC) ?: null;
        }

        return null;
    }
}
