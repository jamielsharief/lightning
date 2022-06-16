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

namespace Lightning\DataMapper\Event;

use Lightning\DataMapper\AbstractDataMapper;

class InitializeEvent
{
    public function __construct(protected AbstractDataMapper $dataMapper)
    {
    }

    /**
     * Get the value of dataMapper
     */
    public function getDataMapper(): AbstractDataMapper
    {
        return $this->dataMapper;
    }

    /**
     * Set the value of dataMapper
     */
    public function setDataMapper(AbstractDataMapper $dataMapper): static
    {
        $this->dataMapper = $dataMapper;

        return $this;
    }
}
