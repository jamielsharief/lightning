<?php declare(strict_types=1);

namespace Lightning\Test\Migration;

use PDO;
use PHPUnit\Framework\TestCase;
use function Lightning\Dotenv\env;
use Lightning\Database\PdoFactory;
use Lightning\Migration\Migration;
use Lightning\Fixture\FixtureManager;
use Lightning\Test\Fixture\MigrationsFixture;

final class SqliteMigrationTest extends TestCase
{
    protected PDO $pdo;
    protected string $migrationFolder;

    protected FixtureManager $fixtureManager;

    public function setUp(): void
    {
        // Create Connection
        $pdoFactory = new PdoFactory(env('DB_URL'), env('DB_USERNAME'), env('DB_PASSWORD'),true);
        $this->pdo = $pdoFactory->create();

        $this->fixtureManager = new FixtureManager($this->pdo);
        $this->fixtureManager->load([MigrationsFixture::class]);

        $this->migrationFolder = sys_get_temp_dir() . '/' . uniqid();
        mkdir($this->migrationFolder);

        if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'sqlite') {
            $this->markTestSkipped('This test is written for Sqlite');
        }
    }

    public function testUp()
    {
        $this->pdo->query('DROP TABLE IF EXISTS posts_m');
        $this->pdo->query('CREATE TABLE posts_m ( "title" TEXT DEFAULT NULL )');

        $migration = new Migration($this->pdo, __DIR__ . '/migrations/sqlite');

        $this->assertEquals('Pending', $migration->get()[1]['status']);

        $this->assertTrue($migration->up());

        $this->assertEquals('Installed', $migration->get()[1]['status']);
    }

    public function testDown()
    {
        $this->pdo->query('DROP TABLE IF EXISTS posts_m');
        $this->pdo->query('CREATE TABLE posts_m ( "title" TEXT DEFAULT NULL )');

        $migration = new Migration($this->pdo, __DIR__ . '/migrations/sqlite');
        $this->assertTrue($migration->up());

        $this->assertEquals('Installed', $migration->get()[1]['status']);

        $this->assertTrue($migration->down());

        $this->assertEquals('Pending', $migration->get()[1]['status']);
    }
}
