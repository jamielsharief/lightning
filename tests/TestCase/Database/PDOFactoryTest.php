<?php declare(strict_types=1);

namespace Lightning\Test\Database;

use PDO;
use PHPUnit\Framework\TestCase;
use function Lightning\Dotenv\env;

use Lightning\Database\PdoFactory;

final class PdoFactoryTest extends TestCase
{
    public function testCreate()
    {
        $pdoFactory = new PdoFactory(env('DB_URL'), env('DB_USERNAME'), env('DB_PASSWORD'),false);
        $pdo = $pdoFactory->create();
        $this->assertInstanceOf(PDO::class, $pdo);
        $this->assertFalse($pdo->getAttribute(PDO::ATTR_PERSISTENT));

        // PHP throws error when trying to get this on SQLLite.  Driver does not support this function: driver does not support that attribute
        if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'sqlite') {
            $this->assertEquals(0, $pdo->getAttribute(PDO::ATTR_EMULATE_PREPARES)); // Strange behavior for false
        }

        $this->assertEquals(PDO::ERRMODE_EXCEPTION, $pdo->getAttribute(PDO::ATTR_ERRMODE));
        $this->assertEquals(PDO::FETCH_ASSOC, $pdo->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE));
    }

    public function testCreatePersistent()
    {
        $pdoFactory = new PdoFactory(env('DB_URL'), env('DB_USERNAME'), env('DB_PASSWORD'),true);

        $pdo = $pdoFactory->create();
        $this->assertInstanceOf(PDO::class, $pdo);
        $this->assertTrue($pdo->getAttribute(PDO::ATTR_PERSISTENT));

    }
}