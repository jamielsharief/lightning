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

namespace Lightning\Dotenv;

use InvalidArgumentException;

/**
 * A simple .env file parser, ignores comment and blank lines.
 */
class Dotenv
{
    private const FILE_OPTIONS = FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES;
    private string $directory;

    /**
     * Constructor
     *
     * @param string $directory
     */
    public function __construct(string $directory)
    {
        if (! is_dir($directory)) {
            throw new InvalidArgumentException(sprintf('`%s` does not exist', $directory));
        }
        $this->directory = $directory;
    }

    /**
     * Loads the env file, the basic parsing rules are this
     *
     * 1. Empty lines are skipped
     * 2. Any lines that start with # are ignored
     * 3. lines are like NAME=value or NAME="some value with space", no spaces before the var name, and no spaces before or after
     *    the equal sign
     * 4. values are always treated as string
     * 5. putenv is not threadsafe
     *
     * @param string $config e.g .env.local .env.testing
     * @return void
     */
    public function load(string $config = '.env'): void
    {
        $path = $this->directory . '/' . $config;

        foreach (file($path, self::FILE_OPTIONS) as $line) {
            // ignore comment lines (start with #)
            if (strpos($line, '#') !== 0) {
                list($name, $value) = explode('=', $line, 2);
                $_ENV[$name] = $value;
            }
        }
    }
}
