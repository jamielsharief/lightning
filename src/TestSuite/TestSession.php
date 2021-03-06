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

namespace Lightning\TestSuite;

/**
 * Test session using default PHP sessions, if you are not using PHP sessions and using a custom
 * class then create your own test session using the TestSessionInterface.
 */
class TestSession implements TestSessionInterface
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $_SESSION = []; // Create session global in CLI
    }

    /**
     * Sets a value in the Session
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Checks if the session has a value
     *
     * @param string $key
     * @return boolean
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $_SESSION);
    }

    /**
     * Gets an item from the Session
     *
     * @param string $key
     * @return mixed
     */
    public function get(string $key): mixed
    {
        return $_SESSION[$key] ?? null;
    }

    /**
     * Clears the Session
     *
     * @return void
     */
    public function clear(): void
    {
        $_SESSION = [];
    }
}
