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
 * BcryptPasswordHasher
 *
 * @see https://www.php.net/manual/en/function.password-hash.php
 */
class BcryptPasswordHasher implements PasswordHasherInterface
{
    /**
     * Hashes the password
     *
     * @param string $pasword
     * @return string
     * @throws InvalidArgumentException
     */
    public function hash(string $pasword): string
    {
        $this->validatePassword($pasword);

        return password_hash($pasword, PASSWORD_BCRYPT);
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
     * BCRYPT truncates passwords after 72 bytes
     *
     * @param string $password
     * @return void
     * @throws InvalidArgumentException
     */
    private function validatePassword(string $password): void
    {
        if ($password === '' || strlen($password) > 72) {
            throw new InvalidArgumentException('Invalid password');
        }
    }
}
