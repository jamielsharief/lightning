<?php declare(strict_types=1);

namespace Lightning\Test\Validator;

use Nyholm\Psr7\ServerRequest;
use Lightning\Validator\Errors;
use PHPUnit\Framework\TestCase;
use Lightning\Validator\Validator;
use Lightning\Entity\AbstractEntity;
use Lightning\Validator\ValidationSet;

class User extends AbstractEntity
{
    private ?int $id = null;
    private ?string $email = null;
    private ?string $password = null;

    public static function fromState(array $state): User
    {
        $user = new static();
        foreach ($state as $key => $value) {
            $user->$key = $value;
        }

        return $user;
    }

    // Gets this object state as an array which is sent to storage
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'password' => $this->password
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }
}

class UserValidator extends Validator
{
    protected function initialize(): void
    {
        $this->createRuleFor('id')
            ->optional()
            ->integer()
            ->lengthBetween(5, 11);

        $this->createRuleFor('email')
            ->notBlank()
            ->email()
            ->lengthBetween(5, 255);

        $this->createRuleFor('password')
            ->notBlank()
            ->method('passwordIsStrong')
            ->lengthBetween(8, 255);
    }

    public function passwordIsStrong(mixed $password): bool
    {
        return is_string($password) && (bool) preg_match('/!/', $password);
    }

    public function confirm(string $password, array $data): bool
    {
        return is_string($password) && $password === $data['password_confirm'];
    }
}

final class ValidatorTest extends TestCase
{
    public function testCreateRules(): void
    {
        $validator = new Validator();

        $this->assertInstanceOf(ValidationSet::class, $validator->createRuleFor('email'));
    }

    public function testGetRules(): void
    {
        $validator = new Validator();
        $validationSet = $validator->createRuleFor('email')->notBlank();
        $rules = $validator->getRules();
        $this->assertIsArray($rules);
        $this->assertEquals($validationSet, $rules['email']);
    }

    public function testGetErrors(): void
    {
        $validator = new Validator();

        $this->assertInstanceOf(Errors::class, $validator->getErrors());
    }

    public function testSetErrors(): void
    {
        $errors = new Errors();
        $errors->setError('foo', 'bar');

        $validator = new Validator();
        $this->assertEquals($errors, $validator->setErrors($errors)->getErrors());
    }

    public function testValidate(): void
    {
        $validator = new Validator();

        $validator->createRuleFor('email')->email();

        $this->assertTrue($validator->validate(['email' => 'foo@example.com']));
        $this->assertEmpty($validator->getErrors()->getError('email'));
    }

    public function testValidateErrorNoData(): void
    {
        $validator = new Validator();

        $validator->createRuleFor('email')->email();

        $this->assertFalse($validator->validate([]));
        $this->assertEquals('invalid email address', $validator->getErrors()->getError('email'));
    }

    public function testValidateErrorRuleFail(): void
    {
        $validator = new Validator();

        $validator->createRuleFor('email')->email();

        $this->assertFalse($validator->validate(['email' => '<-o->']));
        $this->assertEquals('invalid email address', $validator->getErrors()->getError('email'));
    }

    public function testValidateErrors(): void
    {
        $validator = new Validator();

        $validator->createRuleFor('email')->notBlank()->email()->lengthBetween(5, 255);

        $this->assertFalse($validator->validate([]));
        $this->assertEquals('must not be blank', $validator->getErrors()->getError('email'));
        $this->assertCount(3, $validator->getErrors());
    }

    /**
     * @depends testValidate
     */
    public function testValidateOptional(): void
    {
        $validator = new Validator();

        $validator->createRuleFor('email')->optional()->email();

        $this->assertTrue($validator->validate([]));
        $this->assertTrue($validator->validate(['email' => 'foo@example.com']));
    }

    public function testCustomValidator(): void
    {
        $validator = new UserValidator();

        $this->assertTrue($validator->validate([
            'email' => 'foo@example.com',
            'password' => '12345678!'
        ]));

        $this->assertFalse($validator->validate([
            'email' => 'foo@example.com',
            'password' => '12345678'
        ]));

        $validator->createRuleFor('password')
            ->notBlank()
            ->method('confirm')
            ->lengthBetween(8, 255);

        $this->assertTrue($validator->validate([
            'email' => 'foo@example.com',
            'password' => '12345678!',
            'password_confirm' => '12345678!'
        ]));

        $this->assertFalse($validator->validate([
            'email' => 'foo@example.com',
            'password' => '12345678!',
            'password_confirm' => '1234'
        ]));
    }

    public function testCustomValidatorMethod(): void
    {
        $validator = new UserValidator();

        $validator->createRuleFor('password')
            ->notBlank()
            ->method('confirm')
            ->lengthBetween(8, 255);

        // test passing data correctly
        $this->assertTrue($validator->validate([
            'email' => 'foo@example.com',
            'password' => '12345678!',
            'password_confirm' => '12345678!'
        ]));

        $this->assertFalse($validator->validate([
            'email' => 'foo@example.com',
            'password' => '12345678!',
            'password_confirm' => '1234'
        ]));
    }

    public function testValidateObject(): void
    {
        $validator = new UserValidator();

        $entity = User::fromState([
            'email' => 'foo@example.com',
            'password' => '12345678!'
        ]);

        //$this->assertTrue($validator->validate($entity));
        $this->assertFalse($validator->validate($entity->setId(1)));
    }

    public function testValidateServerRequest(): void
    {
        $request = new ServerRequest('POST', 'https://www.example.com/login', []);

        $validator = new UserValidator();
        $this->assertTrue($validator->validate($request->withParsedBody([
            'email' => 'foo@example.com',
            'password' => '12345678!'
        ])));

        $this->assertFalse($validator->validate($request->withParsedBody([
            'email' => '*',
            'password' => '12345678!'
        ])));
    }

    public function testRemove(): void
    {
        $validator = new UserValidator();
        $this->assertArrayHasKey('email', $validator->getRules());
        $this->assertArrayNotHasKey('email', $validator->removeRuleFor('email')->getRules());
    }

    public function testWithoutRule(): void
    {
        $validator = new UserValidator();
        $this->assertArrayHasKey('email', $validator->getRules());
        $this->assertArrayNotHasKey('email', $validator->withoutRuleFor('email')->getRules());
        $this->assertArrayHasKey('email', $validator->getRules());
    }

    public function testStopOnFailure(): void
    {
        $validator = new Validator();

        $validator->createRuleFor('email')
            ->notBlank()
            ->stopOnFailure()
            ->email()
            ->lengthBetween(5, 255);

        $this->assertTrue($validator->validate([
            'email' => 'foo@example.com',
        ]));

        $this->assertFalse($validator->validate([]));
        $this->assertCount(1, $validator->getErrors()); // NotBlank and Email error, lengthBetween should not be run
    }

    public function testStopIfFailure(): void
    {
        $validator = new Validator();

        $validator->createRuleFor('email')
            ->notBlank()
            ->email()
            ->stopIfFailure()
            ->lengthBetween(5, 255);

        $this->assertTrue($validator->validate([
            'email' => 'foo@example.com',
        ]));

        $this->assertFalse($validator->validate([]));
        $this->assertCount(2, $validator->getErrors()); // NotBlank and Email error, lengthBetween should not be run
    }

    public function testHasRuleFor(): void
    {
        $validator = new Validator();
        $this->assertFalse($validator->hasRuleFor('email'));

        $validator->createRuleFor('email')->notBlank();

        $this->assertTrue($validator->hasRuleFor('email'));
    }

    public function testGetRuleFor(): void
    {
        $validator = new Validator();

        $this->assertNull($validator->getRuleFor('email'));

        $emailRule = $validator->createRuleFor('email')
            ->notBlank()
            ->email()
            ->stopIfFailure()
            ->lengthBetween(5, 255);

        $this->assertEquals($emailRule, $validator->getRuleFor('email'));
    }
}
