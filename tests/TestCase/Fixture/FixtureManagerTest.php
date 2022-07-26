<?php declare(strict_types=1);

namespace Lightning\Test\Fixture;

use PDO;
use PHPUnit\Framework\TestCase;
use function Lightning\Dotenv\env;
use Lightning\Database\PdoFactory;

use Lightning\Fixture\FixtureManager;

final class FixtureManagerTest extends TestCase
{
    protected PDO $pdo;
    protected FixtureManager $fixtureManager;

    protected function setUp(): void
    {
        // Create Connection
        $pdoFactory = new PdoFactory(env('DB_URL'), env('DB_USERNAME'), env('DB_PASSWORD'),true);
        $this->pdo = $pdoFactory->create();

        $this->fixtureManager = new FixtureManager($this->pdo);
    }

    public function testLoad(): void
    {
        $this->fixtureManager->load([
            ArticlesFixture::class
        ]);
        $this->assertEquals(3, $this->pdo->query('SELECT COUNT(*) FROM articles')->fetchColumn());
    }

    /**
     * @depends testLoad
     */
    public function testUnload(): void
    {
        $this->fixtureManager->load([
            ArticlesFixture::class
        ]);

        $this->fixtureManager->unload();
        $this->assertEquals(0, $this->pdo->query('SELECT COUNT(*) FROM articles')->fetchColumn());
    }
}
