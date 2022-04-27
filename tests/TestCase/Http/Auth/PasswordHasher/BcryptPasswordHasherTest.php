<?php declare(strict_types=1);

namespace Lightning\Test\Http\Auth\PasswordHasher;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Lightning\Http\Auth\PasswordHasher\BcryptPasswordHasher;

final class BcryptPasswordHasherTest extends TestCase
{
    public function testHash(): void
    {
        $this->assertTrue(password_verify('1234', (new BcryptPasswordHasher())->hash('1234')));
    }

    public function testHashZeroLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid password');

        (new BcryptPasswordHasher())->hash('');
    }

    public function testHashTooLong(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid password');

        (new BcryptPasswordHasher())->hash(str_repeat('x', 73));
    }

    public function testVerifyPassword(): void
    {
        $hash = password_hash('1234', PASSWORD_BCRYPT);
        $this->assertTrue((new BcryptPasswordHasher())->verify('1234', $hash));
    }

    public function testVerifyPasswordZeroLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid password');

        (new BcryptPasswordHasher())->verify('', '<-o->');
    }

    public function testVerifyPasswordTooLong(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid password');

        (new BcryptPasswordHasher())->verify(str_repeat('x', 73), '<-o->');
    }
}
