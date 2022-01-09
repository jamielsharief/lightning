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

namespace Lightning\Logger;

class FileLogger extends AbstractLogger
{
    protected string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return void
     */
    public function log($level, $message, array $context = []): void
    {
        $this->checkLevel($level);

        if ($this->shouldLog($level)) {
            file_put_contents($this->path, $this->format($level, $message, $context) ."\n", FILE_APPEND);
        }
    }
}
