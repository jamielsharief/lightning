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

namespace Lightning\ServiceObject;

use Stringable;
use JsonSerializable;

/**
 * ResultInterface
 */
interface ResultInterface extends JsonSerializable, Stringable
{
    /**
     * Is the a Result a successful result?
     */
    public function isSuccess(): bool;

    /**
     * Checks if a the result has a property with a the name
     */
    public function has(string $name): bool;

    /**
     * Checks if a the result has data
     */
    public function hasData(): bool;

    /**
     * Gets a single entry
     */
    public function get(string $name, mixed $default = null): mixed;

    /**
     * Gets the data of the result
     */
    public function getData(): array;

    /**
     * Returns an instance with the specified data
     */
    public function withData(array $data): static;

    /**
     * Returns an instance with the success status set as
     */
    public function withSuccess(bool $success): static;

    /**
     * Gets the Result object data as an array
     */
    public function toArray(): array;
}
