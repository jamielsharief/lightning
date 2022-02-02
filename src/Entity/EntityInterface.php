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

namespace Lightning\Entity;

use JsonSerializable;

/**
 * Entity Interface
 *
 * When using this make sure that you do not create calculation functions, instead the get method should do the calculation.
 *
 * @see https://martinfowler.com/bliki/AnemicDomainModel.html
 * @see https://martinfowler.com/bliki/POJO.html
 */
interface EntityInterface extends JsonSerializable
{
    /**
     * Create the Entity using data from an array
     *
     * @param array $state
     * @return self
     */
    public static function fromState(array $state): self;

    /**
     * Get the state of the Entity as an array.
     *
     * @return array
     */
    public function toArray(): array;

    /**
     * Checks if the Entity is a new and has not been persisted
     *
     * @return boolean
     */
    public function isNew(): bool;

    /**
     * Marks the Entity persisted state
     *
     * @param boolean $persisted
     * @return void
     */
    public function markPersisted(bool $persisted): void;
}
