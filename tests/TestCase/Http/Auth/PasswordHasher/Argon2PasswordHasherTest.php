<?php declare(strict_types=1);

namespace Lightning\Test\Http\Auth\PasswordHasher;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Lightning\Http\Auth\PasswordHasher\Argon2PasswordHasher;

final class Argon2PasswordHasherTest extends TestCase
{
    public function testHash(): void
    {
        $this->assertTrue(password_verify('1234', (new Argon2PasswordHasher())->hash('1234')));
    }

    public function testHashZeroLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid password');

        (new Argon2PasswordHasher())->hash('');
    }

    public function testHashTooLong(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid password');

        (new Argon2PasswordHasher())->hash(str_repeat('x', 128));
    }

    public function testVerifyPassword(): void
    {
        $hash = password_hash('1234', PASSWORD_ARGON2ID);
        $this->assertTrue((new Argon2PasswordHasher())->verify('1234', $hash));
    }

    public function testVerifyPasswordZeroLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid password');

        (new Argon2PasswordHasher())->verify('', '<-o->');
    }

    public function testVerifyPasswordTooLong(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid password');

        (new Argon2PasswordHasher())->verify(str_repeat('x', 128), '<-o->');
    }
}
