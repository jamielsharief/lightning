<?php declare(strict_types=1);

namespace Lightning\Test\Utility;

use Lightning\Utility\Uuid;
use PHPUnit\Framework\TestCase;

final class UuidTest extends TestCase
{
    public function testPattern(): void
    {
        $this->assertMatchesRegularExpression(Uuid::PATTERN, '123e4567-e89b-12d3-a456-426614174000');
    }

    public function testGenerate(): void
    {
        $this->assertMatchesRegularExpression(Uuid::PATTERN, (new Uuid())->generate());
    }
}
