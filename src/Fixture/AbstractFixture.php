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

use Exception;

/**
 * Undocumented class
 */
abstract class AbstractFixture
{
    protected array $records = [];
    protected string $table;

    public function __construct()
    {
        if (! isset($this->table)) {
            throw new Exception(sprintf('Fixture `%s` does not have table property', get_class($this)));
        }
        $this->initialize();
    }

    protected function initialize(): void
    {
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getRecords(): array
    {
        return $this->records;
    }
}
