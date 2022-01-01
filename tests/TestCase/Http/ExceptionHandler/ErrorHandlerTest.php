<?php declare(strict_types=1);

namespace Lightning\Test\Http\Exception;

use ErrorException;
use PHPUnit\Framework\TestCase;
use Lightning\Http\ExceptionHandler\ErrorHandler;

final class ErrorHandlerTest extends TestCase
{
    public function testRegister()
    {
        $handler = new ErrorHandler();
        $this->assertTrue($handler->register());

        $this->assertEquals(
            [$handler,'handle'],
            set_error_handler([$this,'dummyHandler'])
        );

        restore_error_handler();
    }

    /**
     * @depends testRegister
     */
    public function testHandle(): void
    {
        $handler = new ErrorHandler();
        $this->assertTrue($handler->register());

        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('Deprecated');

        trigger_error('Deprecated', E_USER_DEPRECATED);

        restore_error_handler();
    }

    public function testUnregister()
    {
        $handler = new ErrorHandler();
        $this->assertTrue($handler->unregister());
    }

    public function dummyHandler(int $errno, string $errstr, ?string $errfile = null, ?int $errline = null, ?array $errcontext = null): bool
    {
        return true;
    }
}
