<?php declare(strict_types=1);

namespace Lightning\Test\Http\Exception;

use PHPUnit\Framework\TestCase;
use Lightning\Http\Exception\GoneException;
use Lightning\Http\Exception\HttpException;
use Lightning\Http\Exception\ConflictException;
use Lightning\Http\Exception\NotFoundException;
use Lightning\Http\Exception\ForbiddenException;
use Lightning\Http\Exception\BadRequestException;
use Lightning\Http\Exception\UnauthorizedException;
use Lightning\Http\Exception\NotAcceptableException;
use Lightning\Http\Exception\NotImplementedException;
use Lightning\Http\Exception\MethodNotAllowedException;
use Lightning\Http\Exception\ServiceUnavailableException;
use Lightning\Http\Exception\InternalServerErrorException;

final class ExceptionTest extends TestCase
{
    public function exceptionProvider()
    {
        return [
            [BadRequestException::class,'Bad Request',400],
            [ConflictException::class,'Conflict',409],
            [ForbiddenException::class,'Forbidden',403],
            [GoneException::class,'Gone',410],
            [InternalServerErrorException::class,'Internal Server Error',500],
            [MethodNotAllowedException::class,'Method Not Allowed',405],
            [NotAcceptableException::class,'Not Acceptable',406],
            [NotFoundException::class,'Not Found',404],
            [NotImplementedException::class,'Not Implemented',501],
            [ServiceUnavailableException::class,'Service Unavailable',503],
            [UnauthorizedException::class,'Unauthorized',401]
        ];
    }

    public function testHttpException(): void
    {
        $exception = new HttpException('Proxy Authentication Required', 407);
        $this->assertEquals('Proxy Authentication Required', $exception->getMessage());
        $this->assertSame(407, $exception->getCode());
    }

    /**
     * @dataProvider exceptionProvider
     *
     * @param string $class
     * @param integer $code
     * @return void
     */
    public function testException(string $class, string $message, int $code): void
    {
        $exception = new $class();
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
    }
}
