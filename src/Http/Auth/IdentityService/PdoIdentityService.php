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

namespace Lightning\Http\Auth\IdentityService;

use PDO;
use RuntimeException;
use Lightning\Http\Auth\Identity;
use Lightning\Http\Auth\IdentityServiceInterface;

class PdoIdentityService implements IdentityServiceInterface
{
    private PDO $pdo;
    private string $table = 'users';
    private string $identifierName = 'email';

    /**
     * Constructor
     *
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Gets the identifier name to be used e.g. username, email, token, etc
     *
     * @return string
     */
    public function getIdentifierName(): string
    {
        return $this->identifierName;
    }

    /**
     * Sets the Table
     *
     * @param string $table
     * @return static
     */
    public function setTable(string $table): self
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Sets the identifier name
     *
     * @param string $name
     * @return static
     */
    public function setIdentifierName(string $name): self
    {
        $this->identifierName = $name;

        return $this;
    }

    /**
     * Returns a new storage object with a different name for the identifier
     *
     * @param string $name
     * @return static
     */
    public function withIdentifierName(string $name): self
    {
        return (clone $this)->setIdentifierName($name);
    }

    /**
     * Finds the user by the identifier
     *
     * @param string $identifier username, email, token etc.
     * @return Identity|null
     */
    public function findByIdentifier(string $identifier): ?Identity
    {
        $statement = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE {$this->identifierName} = ?");
        if (! $statement->execute([$identifier])) {
            throw new RuntimeException('Error executing SQL statement');
        }

        $user = $statement->fetch();

        return $user ? new Identity($user) : null;
    }
}
