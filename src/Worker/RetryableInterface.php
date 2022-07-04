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

namespace Lightning\Worker;

interface RetryableInterface
{
    /**
     * Instructs the message object that processing failed
     */
    public function fail(): void;

    /**
     * Gets the number of attempts
     */
    public function attempts(): int ;

    /**
     * Gets the maximum number of times this message processing should be retried
     */
    public function maxRetries(): int;

    /**
     * Seconds to wait before retrying
     */
    public function delay(): int;
}
