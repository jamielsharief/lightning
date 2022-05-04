<?php declare(strict_types=1);

namespace Lightning\Test\Utility;

use Lightning\Utility\Uuid;
use PHPUnit\Framework\TestCase;

final class UuidTest extends TestCase
{
    public function testPattern(): void
    {
        $this->assertMatchesRegularExpression(Uuid::PATTERN, '8a86564a-b826-406e-a7fa-3bcb51fffa98');
    }

    public function testGenerate(): void
    {
        $this->assertMatchesRegularExpression(Uuid::PATTERN, (new Uuid())->generate());
    }
}
