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

class MysqlDialect implements SqlDialectInterface
{
    public function disableForeignKeyConstraints(): array
    {
        return [
            'SET FOREIGN_KEY_CHECKS = 0'
        ];
    }

    public function enableForeignKeyConstraints(): array
    {
        return [
            'SET FOREIGN_KEY_CHECKS = 1'
        ];
    }

    public function truncate(string $table): array
    {
        return [
            sprintf('TRUNCATE TABLE %s', $this->quoteIdentifier($table))
        ];
    }

    public function quoteIdentifier(string $identifier): string
    {
        return sprintf('`%s`', $identifier);
    }
}
