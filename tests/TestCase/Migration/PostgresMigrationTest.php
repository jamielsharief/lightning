<?php declare(strict_types=1);

namespace Lightning\Test\Migration;

use PDO;
use PHPUnit\Framework\TestCase;
use function Lightning\Dotenv\env;
use Lightning\Database\PdoFactory;
use Lightning\Migration\Migration;
use Lightning\Fixture\FixtureManager;

use Lightning\Test\Fixture\MigrationsFixture;

final class PostgresMigrationTest extends TestCase
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

        if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'pgsql') {
            $this->markTestSkipped('This test is written for Postgres');
        }
    }

    public function testGetMigrations()
    {
        $migration = new Migration($this->pdo, __DIR__ . '/migrations/pgsql');
        $expected = [
            [
                'version' => 1,
                'name' => 'Initial Setup',
                'path' => __DIR__ . '/migrations/pgsql/v1_initial_setup.sql',
                'installed_on' => '2021-09-28 16:10:00',
                'status' => 'Installed'
            ],
            [
                'version' => 2,
                'name' => 'Add Index Posts',
                'path' => __DIR__ . '/migrations/pgsql/v2_add_index_posts.sql',
                'installed_on' => null,
                'status' => 'Pending',
            ]
        ];

        $this->assertEquals(
            $expected,
            $migration->get()
        );
    }

    public function testUp()
    {
        $this->pdo->query('DROP TABLE IF EXISTS posts_m');
        $this->pdo->query('CREATE TABLE posts_m ( title VARCHAR(50) )');

        $migration = new Migration($this->pdo, __DIR__ . '/migrations/pgsql');

        $this->assertEquals('Pending', $migration->get()[1]['status']);

        $this->assertTrue($migration->up());

        $this->assertEquals('Installed', $migration->get()[1]['status']);
    }

    public function testUpWithCallback()
    {
        $this->pdo->query('DROP TABLE IF EXISTS posts_m');
        $this->pdo->query('CREATE TABLE posts_m ( title VARCHAR(50) )');

        $migration = new Migration($this->pdo, __DIR__ . '/migrations/pgsql');
        $this->assertTrue($migration->up(function ($payload) {
            $this->assertSame('Add Index Posts', $payload['name']);
            $this->assertSame('CREATE INDEX idx_title ON posts_m (title)', $payload['statements'][0]);
        }));
    }

    public function testDown()
    {
        $this->pdo->query('DROP TABLE IF EXISTS posts_m');
        $this->pdo->query('CREATE TABLE posts_m ( title VARCHAR(50) )');

        $migration = new Migration($this->pdo, __DIR__ . '/migrations/pgsql');
        $this->assertTrue($migration->up());

        $this->assertEquals('Installed', $migration->get()[1]['status']);

        $this->assertTrue($migration->down());

        $this->assertEquals('Pending', $migration->get()[1]['status']);
    }

    public function testDownWithCallback()
    {
        $this->pdo->query('DROP TABLE IF EXISTS posts_m');
        $this->pdo->query('CREATE TABLE posts_m ( title VARCHAR(50) )');

        $migration = new Migration($this->pdo, __DIR__ . '/migrations/pgsql');

        $this->assertTrue($migration->up());

        $this->assertTrue($migration->down(function ($payload) {
            $this->assertSame('Add Index Posts', $payload['name']);
            $this->assertSame('DROP INDEX idx_title', $payload['statements'][0]);
        }));
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->unload();
    }
}
