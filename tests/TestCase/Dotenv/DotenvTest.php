<?php declare(strict_types=1);

namespace Lightning\Test\Dotenv;

use Lightning\Dotenv\Dotenv;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class DotenvTest extends TestCase
{
    public function testLoad(): void
    {
        $id = uniqid();
        $file = sys_get_temp_dir() . '/' . $id;
        file_put_contents($file, implode("\n", ['# This is a test', '', 'FOO=bar']));

        $dotEnv = new Dotenv(sys_get_temp_dir());

        $this->assertFalse(isset($_ENV['FOO']));

        $dotEnv->load($id);

        $this->assertEquals('bar', $_ENV['FOO'] ?? null);
    }

    public function testInvalidDirectory(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('`/nowhere` does not exist');
        new Dotenv('/nowhere');
    }
}
