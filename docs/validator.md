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

    public function confirm(mixed $password, array $data) : bool 
    {
        return is_string($password) && isset($data['password_confirm']) && $password === $data['password_confirm'];
    }
}
```

Create the validator object

```php
$validator  = new UserValidator();
```

To validate a an array of data

```php
// validate an array
$result = $validator->validate($_POST);

// You can also validate value objects
$validator->validate(new UserEntity());

// validate PSR Server Request object
$validator->validate($serverRequest);
```

You can also just use the `Validator` as a generic validator.

```php
$validator = new Validator();
$validator->createRuleFor('email')
    ->notBlank()
    ->email()
    ->lengthBetween(5,255);
$validator->validate($_POST);
```


## Validation rules

- alpha
- alphaNumeric
- notNull
- notEmpty
- notBlank
- email
- in
- notIn
- length
- lengthBetween
- minLength
- maxLength
- greaterThanOrEqualTo
- greaterThan
- lessThanOrEqualTo
- lessThan
- equalTo
- notEqualTo
- range
- integer
- string
- numeric
- decimal
- array
- date (format)
- datetime (format)
- time (format)
- before (date)
- after  (date)

Special rules

- `optional` If data is empty validation rules are skipped for that field/property.
- `stopOnFailure` - Any validation rule will immediately stop running any subsequent rules, calling this will ensure that only one error is every returned.
- `stopIfFailure` - If there are any validation errors for this field/property, then stop, do not run anymore validation rules. For example, if you are validating DNS records for a domain, but its an invalid domain, then there is no point checking the DNS records.


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
