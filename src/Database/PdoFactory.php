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

namespace Lightning\Database;

use PDO;

/**
 * PdoFactory class
 */
class PdoFactory implements PdoFactoryInterface
{
    /**
     * Constructor
     *
     * @param string $dsn e.g. mysql:host=127.0.0.1;port=3306;dbname=crm;charset=utf8mb4
     */
    public function __construct(
        private string $dsn,
        private string $username, 
        private string $password, 
        private bool $persistent = false)
    {
    }

    /**
     * Factory method
     */
    public function create(): PDO
    {
        return new PDO($this->dsn, $this->username, $this->password, [

            /**
             * don't set to true unless you know what you are doing, this can have all kinds of effects
             * that need to be understood properly.
             */
            PDO::ATTR_PERSISTENT => $this->persistent,
            /**
             * 1. This must be set to false for security reasons
             * 2. It also plays a part in cast in casting data types such as integer
             */
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }
}
