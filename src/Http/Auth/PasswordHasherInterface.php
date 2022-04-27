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

namespace Lightning\Http\Auth;

/**
 * @see https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html
 */
interface PasswordHasherInterface
{
    /**
     * Hashes a password
     *
     * @param string $pasword
     * @return string
     */
    public function hash(string $pasword): string;

    /**
     * Checks a plain text password againt the hashed version
     *
     * @param string $password
     * @param string $hashedPassword
     * @return boolean
     */
    public function verify(string $password, string $hashedPassword): bool;
}
