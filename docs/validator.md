# Validator

Create a class extending `Validator` and configure the validation rules in the `initialize` method.

```php
class UserValidator extends Validator
{
    protected function initialize() : void 
    {
        $this->createRuleFor('id')
            ->optional() 
            ->integer() 
            ->lengthBetween(5,11)

        $this->createRuleFor('email')
            ->notBlank()
            ->email()
            ->lengthBetween(5,255);
        
        $this->createRuleFor('password')
            ->notBlank()
            ->regularExpression('/^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$/');
            ->method('confirm')
            ->lengthBetween(8,255);
    }

    public function confirm(string $password, array $data) : bool 
    {
        return $password === $data['password_confirm'];
    }
}
```

To validate a an array of data

```php
$validator  = new UserValidator();

// validate an array
$result = $validator->validate($_POST);

// You can also validate value objects
$validator->validate(new UserEntity());

// validate PSR Server Request object
$validator->validate($serverRequest);
```

## Errors Object

To work with the `Errors` object

```php
$bool = $errors->hasErrors();
$bool = $errors->hasErrors('email');

$array = $errors->getErrors();
$array = $errors->getErrors('email');
$message = $errors->getError('email'); // get the first error

$errors->setError('email','invalid email address');
```
