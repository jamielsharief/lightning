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

namespace Lightning\Http\Auth\PasswordHasher;

use InvalidArgumentException;
use Lightning\Http\Auth\PasswordHasherInterface;

/**
 * Argon2 password hasher uses the argon2id algo which is recommended by OWASP as the first choice, then second choice Bcrypt.
 *
 * @see https://www.php.net/manual/en/function.password-hash.php
 * @see https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html
 */
class Argon2PasswordHasher implements PasswordHasherInterface
{
    /**
     * Hashes the password
     *
     * @param string $pasword
     * @return string e.g. $argon2id$v=19$m=65536,t=4,p=1$NDRPckVJeDBSWGszRWh3NQ$Ggx88KtnUm+r59Vhqm8+qEzEHYXX8sgwDYr683oF2LM
     * @throws InvalidArgumentException
     */
    public function hash(string $pasword): string
    {
        $this->validatePassword($pasword);

        return password_hash($pasword, PASSWORD_ARGON2ID);
    }

    /**
     * Verifies the password against the hashed password
     *
     * @param string $password
     * @param string $hash
     * @return boolean
     * @throws InvalidArgumentException
     */
    public function verify(string $password, string $hash): bool
    {
        $this->validatePassword($password);

        return password_verify($password, $hash);
    }

    /**
     * Check the password is not empty or exceeds what will be used by the pasword hashing.
     * The argon command line tool limits to 127 yet wikipedia shows 4294967295 bytes as the limit.
     *
     * @param string $password
     * @return void
     * @throws InvalidArgumentException
     */
    private function validatePassword(string $password): void
    {
        if ($password === '' || strlen($password) > 127) {
            throw new InvalidArgumentException('Invalid password');
        }
    }
}
