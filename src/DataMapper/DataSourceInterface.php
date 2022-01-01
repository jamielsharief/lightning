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

namespace Lightning\DataMapper;

interface DataSourceInterface
{
    public function create(string $table, array $data): bool;
    public function read(string $table, QueryObject $query): ResultSet;
    public function update(string $table, QueryObject $query, array $data): int;
    public function delete(string $table, QueryObject $query): int;
    public function count(string $table, QueryObject $query): int;

    /**
     * Gets the Generated ID by the datasource
     *
     * @return int|string|null
     */
    public function getGeneratedId();
}
