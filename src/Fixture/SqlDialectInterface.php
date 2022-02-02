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

namespace Lightning\Fixture;

interface SqlDialectInterface
{
    public function disableForeignKeyConstraints(): array;

    public function enableForeignKeyConstraints(): array;

    public function truncate(string $table): array;

    public function quoteIdentifier(string $identifier): string;

    public function resetAutoIncrement(string $table, int $id, string $column = 'id'): array;
}
