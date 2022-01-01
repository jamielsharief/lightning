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

namespace Lightning\TestSuite\Stubs;

use Psr\Log\LogLevel;
use Psr\Log\LoggerTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\InvalidArgumentException;

/**
 * A Stub for PSR-3 Logger
 */
class LoggerStub implements LoggerInterface
{
    use LoggerTrait;

    protected array $logLevels = [
        LogLevel::DEBUG,
        LogLevel::INFO,
        LogLevel::NOTICE,
        LogLevel::WARNING,
        LogLevel::ERROR,
        LogLevel::CRITICAL,
        LogLevel::ALERT,
        LogLevel::EMERGENCY
    ];

    protected array $logged = [];

    /**
    * Logs with an arbitrary level.
    *
    * @param mixed   $level
    * @param string  $message
    * @param mixed[] $context
    *
    * @return void
    *
    * @throws \Psr\Log\InvalidArgumentException
    */
    public function log($level, $message, array $context = [])
    {
        if (! in_array($level, $this->logLevels)) {
            throw new InvalidArgumentException(sprintf('Unkown log level `%s`', $level));
        }
        $this->logged[] = [
            $level, $message, $context
        ];
    }

    /**
     * Gets the logged messages
     *
     * @return array
     */
    public function getLogged(): array
    {
        return $this->logged;
    }
}
