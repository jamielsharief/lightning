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
 * Attach this to your models etc to use with  your existing setups.
 */
interface IdentityServiceInterface
{
    /**
     * Get the identifier name e.g. username, email, token etc
     */
    public function getIdentifierName(): string;

    /**
     * Gets the credential name e.g. password, hashed_password
     */
    public function getCredentialName(): string;

    /**
     * Finds the user details by the provided identifier
     *
     * @param string $identifier    username, email, token etc
     */
    public function findByIdentifier(string $identifier): ?Identity;
}
