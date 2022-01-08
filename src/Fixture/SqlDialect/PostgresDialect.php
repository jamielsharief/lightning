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

namespace Lightning\Fixture\SqlDialect;

use Lightning\Fixture\SqlDialectInterface;

class PostgresDialect implements SqlDialectInterface
{
    public function disableForeignKeyConstraints(): array
    {
        return [
            'SET CONSTRAINTS ALL IMMEDIATE'
        ];
    }

    public function enableForeignKeyConstraints(): array
    {
        return [
            'SET CONSTRAINTS ALL DEFERRED'
        ];
    }

    public function truncate(string $table): array
    {
        return [
            sprintf('TRUNCATE TABLE %s RESTART IDENTITY CASCADE', $this->quoteIdentifier($table))
        ];
    }

    public function quoteIdentifier(string $identifier): string
    {
        return sprintf('"%s"', $identifier);
    }

    public function resetAutoIncrement(string $table, int $id, string $column = 'id'): array
    {
        return [
            sprintf('ALTER SEQUENCE %s_%s_seq RESTART WITH %d', $table, $column, $id)
        ];
    }
}
