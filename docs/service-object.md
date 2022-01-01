# Service Object

`Service Objects` help keep your models and controllers skinny, whilst keeping your code clean and testable. The `Service Object` also helps you seperate your application business logic from the framework and also makes it easy to test in isolation. `Service Objects` have all dependencies added to the `__constructor`.

The `Service Object` is based upon the command pattern and follows the [single responsibility principle](https://en.wikipedia.org/wiki/Single-responsibility_principle), with the method `execute` where the application business logic goes and it must always return a `Result` object, standardizing the result is an important part of this design.

This package provides:

- `AbstractServiceObject` which also is a `callable`, so it can be executed now or later.
- The `Result` value object
- `Params` an array object for passing context parameters to the `ServiceObject`

## Usage

Create a class with depdencies in the `__construct` method and place the busines logic in the `execute` method, which must return the `Result` object.

```php
class RegisterUserService extends AbstractServiceObject
{
    public function __construct(private Model $user, private LoggerInterface $logger) 
    {
    }

    protected function execute(Params $params, Result $result) : Result 
    {
        $user = $params->get('user');
        // do some stuff
        return $result->withData(['status'=>'ok']);
    }
}
```

To run the service

```php
$params = new Params(['name'=>'fred', 'email'=>'fred@example.com']);
$result = (new RegisterUserService ($model,$logger))
    ->withParams($params)
    ->run();
```

## Params Object

The `Params` object is used to pass parameters to the `Service Object`, when call `get` if the param was not supplied it will throw `UnkownParameterException`, therefore, for optional parameters check with `has` first.

```php
$params = new Params(['name'=>'fred', 'email'=>'fred@example.com']);
$name = $params->get('name');
$bool = $params->has('surname');
```

## Result Object

Depending what the service layer is doing sometimes you will need to just return a simple true or false and other times you will need more information. The `ResultObject` allows you work with rich results.

To create a  `Result` object

```php
$result = new Result(false);
$result = new Result(true, ['message' => 'ok']);
```

Methods available on `Result` object

```php
// check status
$result->isSuccess();
$result->isError();

// work with data
$result->hasData();
$result->getData();
$result->getData('message');
$string = (string) $result;
$result = $result->withSuccess(false);
$result = $result->withData(['key' => 'value']);
```