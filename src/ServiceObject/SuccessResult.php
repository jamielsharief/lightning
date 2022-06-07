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

use LogicException;

class SuccessResult extends Result
{
    /**
     * Constructor
     */
    public function __construct(array $data = [])
    {
        parent::__construct(true, $data);
    }

    public function withSuccess(bool $success): static
    {
        throw new LogicException('The success status cannot be changed on SuccessResult');
    }
}
