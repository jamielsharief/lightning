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

namespace Lightning\Fixture\SqlDialect;

use Lightning\Fixture\SqlDialectInterface;

class SqliteDialect implements SqlDialectInterface
{
    public function disableForeignKeyConstraints(): array
    {
        return [
            'PRAGMA foreign_keys = OFF'
        ];
    }

    public function enableForeignKeyConstraints(): array
    {
        return [
            'PRAGMA foreign_keys = ON'
        ];
    }

    public function truncate(string $table): array
    {
        return [
            sprintf('DELETE FROM %s', $this->quoteIdentifier($table)),
            sprintf('DELETE FROM sqlite_sequence WHERE name = %s', $this->quoteIdentifier($table)),
        ];
    }

    public function quoteIdentifier(string $identifier): string
    {
        return sprintf('"%s"', $identifier);
    }

    public function resetAutoIncrement(string $table, int $id, string $column = 'id'): array
    {
        return [
            sprintf('SQLITE_SEQUENCE SET SEQ = %d WHERE NAME = %s', $id, $this->quoteIdentifier($table))
        ];
    }
}
