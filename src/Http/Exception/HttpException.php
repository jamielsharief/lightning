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

namespace Lightning\Http\Exception;

use Exception;
use Throwable;

/**
 * @see https://httpstatuses.com/
 */
class HttpException extends Exception
{
    /**
     * Constructor
     *
     * @param string $message
     * @param Throwable|null $previous
     */
    public function __construct(string $message, int $statusCode, ?Throwable $previous = null)
    {
        parent::__construct($message, $statusCode, $previous);
    }
}
