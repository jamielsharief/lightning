# Service Object

Service objects help keep your models and controllers skinny, whilst keeping your code clean and testable. The service object also helps you seperate your application business logic from the framework and also makes it easy to test in isolation. 

`Service Objects` must have all dependencies added to the `__constructor` method.

The `Service Object` is based upon the command pattern and follows the [single responsibility principle](https://en.wikipedia.org/wiki/Single-responsibility_principle), with the protected method `execute` where the application business logic goes and it must always return a `ResultInterface` object, standardizing the result is also an important part of this design.

## Usage

Create a class with depdencies in the `__construct` method and place the busines logic in the `execute` method, which must return a `ResultInterface` object.

```php
class RegisterUserService extends AbstractServiceObject
{
    public function __construct(private Model $user, private LoggerInterface $logger) 
    {
    }

    protected function execute(Params $params) : Result
    {
        $user = $params->get('user');

        if(!$user){
            return new ErrorResult(['foo' =>'bar']);
        }
        // do some stuff
        return new SuccessResult(['status'=>'ok']);
    }
}
```

To run the service

```php
$result = (new RegisterUserService ($model, $logger))
    ->withParams(['name' => 'fred', 'email' => 'fred@example.com'])
    ->run();
```

## Params Object

The `Params` object is passed to the execute method when the `ServiceObject` is `run`. If `get` is called and the parameter was not supplied it will throw `UnkownParameterException`, therefore, for optional parameters check with `has` first.

```php
$params = new Params(['name' => 'fred', 'email' => 'fred@example.com']);
$name = $params->get('name');
$bool = $params->has('surname');
```

## Result Objects

Depending what the service layer is doing sometimes you will need to just return a simple `true` or `false` and other times you will need a richer result. 

```php
$result = new ErrorResult(['message' => 'This is an error']);
$result = new SuccessResult(['id'=> 1234]); 
```

Some of the methods available on `Result` object:

```php
// check status
$result->isSuccess();
$result->isError();

// work with data
$result->hasData();
$result->get('message');
$string = (string) $result;
$result = $result->withSuccess(false); // 
$result = $result->withData(['key' => 'value']);
```