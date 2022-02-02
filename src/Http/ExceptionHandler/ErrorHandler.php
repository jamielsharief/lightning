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

namespace Lightning\Http\ExceptionHandler;

use ErrorException;

/**
 * ErrorHandler
 *
 * Convert all errors to exceptions but still work with the error control operator @
 */
class ErrorHandler
{
    /**
     * Registers the ErrorHandler
     *
     * @see https://www.php.net/manual/en/function.set-error-handler.php
     *
     * @return bool
     */
    public function register(): bool
    {
        return set_error_handler([$this,'handle']) !== null;
    }

    /**
     * Unregisters the error handler
     *
     * @return boolean
     */
    public function unregister(): bool
    {
        return restore_error_handler();
    }

    /**
     * Handler
     *
     * @example $result = @file('/does-not-exist');
     *
     * @see https://www.php.net/manual/en/language.operators.errorcontrol.php
     *
     * @param integer $errno
     * @param string $errstr
     * @param string|null $errfile
     * @param integer|null $errline
     * @param array|null $errcontext
     * @return boolean
     */
    public function handle(int $errno, string $errstr, ?string $errfile = null, ?int $errline = null, ?array $errcontext = null): bool
    {
        if (! (error_reporting() & $errno)) {
            return false; // tis was silenced
        }

        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
}
