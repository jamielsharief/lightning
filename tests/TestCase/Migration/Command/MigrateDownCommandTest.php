<?php declare(strict_types=1);

namespace App\Command;

use PDO;
use PHPUnit\Framework\TestCase;
use function Lightning\Dotenv\env;
use Lightning\Database\PdoFactory;
use Lightning\Migration\Migration;
use Lightning\Fixture\FixtureManager;
use Lightning\Console\ConsoleArgumentParser;
use Lightning\Test\Fixture\MigrationsFixture;

use Lightning\Console\TestSuite\TestConsoleIo;
use Lightning\Migration\Command\MigrateUpCommand;
use Lightning\Migration\Command\MigrateDownCommand;
use Lightning\Console\TestSuite\ConsoleIntegrationTestTrait;

final class MigrateDownCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    protected PDO $pdo;
    protected string $migrationFolder;

    protected FixtureManager $fixtureManager;

    public function setUp(): void
    {
        // Create Connection
        $pdoFactory = new PdoFactory(env('DB_URL'), env('DB_USERNAME'), env('DB_PASSWORD'),true);
        $this->pdo = $pdoFactory->create();

        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        $this->fixtureManager = new FixtureManager($this->pdo);
        $this->fixtureManager->load([MigrationsFixture::class]);

        $this->fixtureManager->truncate('migrations'); // reset db

        $this->pdo->query('DROP TABLE IF EXISTS posts_m');
        $this->pdo->query('DROP TABLE IF EXISTS articles_m');

        $migration = new Migration($this->pdo, dirname(__DIR__). '/migrations/' . $driver);
        $command = new MigrateDownCommand(new ConsoleArgumentParser(), new TestConsoleIo(), $migration);
        $this->setupIntegrationTesting($command);
    }

    public function testMigrate(): void
    {
        // run migrations so we can undom them
        $migration = new Migration($this->pdo, dirname(__DIR__). '/migrations/' . $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME));
        $command = new MigrateUpCommand(new ConsoleArgumentParser(), new TestConsoleIo(), $migration);
        $command->run([]);

        $this->execute();
        $this->assertExitSuccess();
        $this->assertOutputContains('Rolling back migration <info>Add Index Posts</info>');
        $this->assertOutputContains('Ran 1 migration(s)');
    }

    public function testMigrateNothing(): void
    {
        $this->execute();
        $this->assertExitSuccess();
        $this->assertOutputContains('Ran 0 migration(s)');
    }
}
