<?php declare(strict_types=1);

namespace Lightning\Test\ServiceObject;

use PHPUnit\Framework\TestCase;
use Lightning\ServiceObject\SuccessResult;

final class SuccessResultTest extends TestCase
{
    public function testConstructor(): void
    {
        $result = new SuccessResult(['foo' => 'bar']);

        // $this->assertFalse($result->isError());
        $this->assertTrue($result->isSuccess());
    }
}
