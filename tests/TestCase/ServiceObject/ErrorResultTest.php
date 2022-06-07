<?php declare(strict_types=1);

namespace Lightning\Test\ServiceObject;

use PHPUnit\Framework\TestCase;
use Lightning\ServiceObject\ErrorResult;

final class ErrorResultTest extends TestCase
{
    public function testConstructor(): void
    {
        $result = new ErrorResult(['foo' => 'bar']);

        // $this->assertTrue($result->isError());
        $this->assertFalse($result->isSuccess());
    }
}
