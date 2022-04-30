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

namespace Lightning\Console\TestSuite;

use RuntimeException;
use Lightning\Console\ConsoleIo;

final class TestConsoleIo extends ConsoleIo
{
    protected string $stdoutOutput = '';
    protected string $stderrOutput = '';
    protected array $stdinInput = [];

    private int $current = -1;

    /**
     * Constructor
     *
     * Change default to RAW
     */
    public function __construct()
    {
        parent::__construct(); // hie o

        $this->outputMode = self::RAW;
    }

    protected function writeStderr(string $data): void
    {
        $this->stderrOutput .= $data;
    }

    protected function writeStdout(string $data): void
    {
        $this->stdoutOutput .= $data;
    }

    protected function readStdin(): string
    {
        $this->current ++;

        if (! isset($this->stdinInput[$this->current])) {
            throw new RuntimeException('Console input is requesting more input that what was provided');
        }

        return $this->stdinInput[$this->current];
    }

    public function getStdout(): string
    {
        return $this->stdoutOutput;
    }

    public function getStderr(): string
    {
        return $this->stderrOutput;
    }

    /**
     * Sets the input
     *
     * @param array $input
     * @return static
     */
    public function setInput(array $input): static
    {
        $this->stdinInput = $input;

        return $this;
    }

    /**
     * Resets the IO object
     *
     * @return void
     */
    public function reset(): void
    {
        $this->stdoutOutput = '';
        $this->stderrOutput = '';
        $this->stdinInput = [];
    }
}
