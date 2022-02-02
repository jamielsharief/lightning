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

namespace Lightning\Migration;

use PDO;

class Migration
{
    private PDO $pdo;
    private string $path;
    private string $table = 'migrations';

    /**
     * Constructor
     *
     * @param PDO $pdo
     * @param string $path
     */
    public function __construct(PDO $pdo, string $path)
    {
        $this->pdo = $pdo;
        $this->path = $path;
    }

    /**
     * Gets the Migrations and information
     *
     * @return array
     */
    public function get(): array
    {
        $migrations = [];
        foreach (glob($this->path . '/*.sql') as $path) {
            $migrations[] = $this->parseMigration($path);
        }

        return $migrations;
    }

    /**
     * Migrates the database up
     *
     * @param callable|null $callback
     * @return boolean
     */
    public function up(?callable $callback = null): bool
    {
        foreach ($this->get() as $migration) {
            if ($migration['status'] === 'Installed') {
                continue;
            }

            $migration['statements'] = [];

            $statements = $this->parseSQL(file_get_contents($migration['path']));

            foreach ($statements['up'] as $statement) {
                if (! $this->pdo->prepare($statement)->execute()) {
                    return false;
                }
                $migration['statements'][] = $statement;
            }
            $this->pdo->prepare("INSERT INTO {$this->table} (version) VALUES (?)")->execute([$migration['version']]);

            if ($callback) {
                $callback($migration);
            }
        }

        return true;
    }

    /**
     * Rolls the migration down one version
     *
     * @param callable|null $callback
     * @return boolean
     */
    public function down(?callable $callback = null): bool
    {
        $migrations = $this->get();
        krsort($migrations);

        foreach ($migrations  as $migration) {
            if ($migration['status'] === 'Pending') {
                continue;
            }

            $migration['statements'] = [];

            $statements = $this->parseSQL(file_get_contents($migration['path']));

            foreach ($statements['down'] as $statement) {
                if (! $this->pdo->prepare($statement)->execute()) {
                    return false;
                }
                $migration['statements'][] = $statement;
            }
            $this->pdo->prepare("DELETE FROM {$this->table} WHERE version = ?")->execute([$migration['version']]);
            if ($callback) {
                $callback($migration);
            }

            break; // Only go one down
        }

        return true;
    }

    private function parseMigration(string $path): array
    {
        preg_match('/v([\d]+)_(.*)/', basename($path, '.sql'), $matches);

        $version = (int) $matches[1];

        $migration = $this->findByVersion($version);

        return [
            'version' => $version,
            'name' => ucwords(str_replace('_', ' ', $matches[2])),
            'path' => $path,
            'installed_on' => $migration['created_at'] ?? null,
            'status' => $migration ? 'Installed' : 'Pending'
        ];
    }

    /**
     * Searches the database for
     *
     * @param integer $version
     * @return array
     */
    private function findByVersion(int $version): array
    {
        $statement = $this->pdo->prepare('SELECT * FROM migrations WHERE version = ?');
        $statement->execute([$version]);

        $result = $statement->fetch(PDO::FETCH_ASSOC);

        return $result ?: [];
    }

    /**
     * Parses the UP/DOWN from the SQL file
     *
     * @param string $sql
     * @return array
     */
    private function parseSQL(string $sql): array
    {
        $up = $down = [];

        $captureUp = $captureDown = false;

        foreach (explode(';' . PHP_EOL, $sql) as $statement) {
            $statement = trim(trim($statement), PHP_EOL . ';');

            if (! $statement) {
                continue;
            }

            if (! $captureUp && preg_match('/^-- Up/i', $statement)) {
                $captureUp = true;
                $captureDown = false;
                $statement = preg_replace('/^(-- Up' . PHP_EOL . ')/i', '', $statement);
            }

            if (! $captureDown && preg_match('/^-- Down/i', $statement)) {
                $captureDown = true;
                $captureUp = false;
                $statement = preg_replace('/^(-- Down' . PHP_EOL . ')/i', '', $statement);
            }

            if ($captureUp) {
                $up[] = $statement;
            } elseif ($captureDown) {
                $down[] = $statement;
            }
        }

        return ['up' => $up,'down' => $down];
    }
}
