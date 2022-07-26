<?php declare(strict_types=1);

namespace Lightning\Test\Http\Auth\IdentityService;

use PDO;
use PHPUnit\Framework\TestCase;
use Lightning\Http\Auth\Identity;
use function Lightning\Dotenv\env;

use Lightning\Database\PdoFactory;
use Lightning\Fixture\FixtureManager;
use Lightning\Test\Fixture\IdentitiesFixture;
use Lightning\Http\Auth\IdentityService\PdoIdentityService;

final class PdoIdentityServiceTest extends TestCase
{
    private PDO $pdo;

    public function setUp(): void
    {
        $pdoFactory = new PdoFactory(env('DB_URL'), env('DB_USERNAME'), env('DB_PASSWORD'),true);
        $this->pdo = $pdoFactory->create();

        $this->fixtureManager = new FixtureManager($this->pdo);
        $this->fixtureManager->load([
            IdentitiesFixture::class
        ]);
    }

    public function testGetIdentifier(): void
    {
        $identityService = (new PdoIdentityService($this->pdo))
            ->setTable('identities');

        $this->assertEquals('email', $identityService->getIdentifierName());
        $this->assertEquals('token', $identityService->setIdentifierName('token')->getIdentifierName());
    }

    public function testGetCredential(): void
    {
        $identityService = (new PdoIdentityService($this->pdo))
            ->setTable('identities');
        $this->assertEquals('password', $identityService->getCredentialName());
        $this->assertEquals('foo', $identityService->setCredentialName('foo')->getCredentialName());
    }

    public function testFindByIdentifier(): void
    {
        $identityService = (new PdoIdentityService($this->pdo))
            ->setTable('identities')
            ->setIdentifierName('username');

        $this->assertInstanceOf(Identity::class, $identityService->findByIdentifier('user1@example.com'));
    }

    public function testFindByIdentifierException(): void
    {
        $identityService = (new PdoIdentityService($this->pdo))
            ->setTable('identities')
            ->setIdentifierName('username');

        $this->assertNull($identityService->findByIdentifier('foo@example.com'));
    }
}
