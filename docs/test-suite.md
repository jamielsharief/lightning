# Testsuite

A PSR friendly test suite.

## Usage

Add the `IntegrationTestTrait` to your test, you will need a PSR 17 Server Request and Response Factory implementation
and a `RequestHandler`. You may need to create your own using your own router etc.


```php
use Lightning\TestSuite\RequestHandlerFactory;

final class ArticlesControllerTest extends TestCase
{
    use IntegrationTestTrait;

    public function setUp(): void
    {
        // Create DI Container
        $definitions = include dirname(__DIR__, 3) . '/config/services.php';
        $container = new Container($definitions);
        $container->enableAutowiring()
                  ->enableAutoConfiguration();

        $requestHandler = $container->get(Router::class); // RequestHandlerInterface

        // Setup Integration testing
        $this->setupIntegrationTesting(
            new ServerRequestFactory(new Psr17ServerRequestFactory()), new Psr17ResponseFactory(), $requestHandler, new TestSession()
        );
    }
}
```

Now in your tests you have a number of assertions


```php
public function testIndex() : void 
{
    $this->get('/articles/index');
    $this->assertResponseCode(200);
    $this->assertResponseContains('<h1>Articles</h1>');
}
```

For a test you might want to set the headers which will be added to the `ServerRequestInterface` request object

```php
$this->setHeaders([
    'PHP_AUTH_USER' => 'somebody@example.com'
]);
```

To add `Cookies` to the `ServerRequestInterface` request object

```php
$this->setCookieParams([
    'user_id' => '1234'
]);
```

You can also set `$_SERVER` vars which will be added to the `ServerRequestInterface` request object

```php
$this->setServerParams([
    'HTTP_REFERER' => 'https://www.google.co.uk'
]);
```

If you are testing file uploads, add your `UploadedFileInterface` objects, this will be added to the `ServerRequestInterface` request object, so then `getUploadedFiles` works as expected.

```php
$this->setUploadedFiles([
    'image_upload' => new UploadedFile(
        '/var/www/tests/files/README.txt',
        1024,
        UPLOAD_ERR_OK,
        'README.txt',
        'text/plain'
    )
]);
```

To set items in the `Session`, the default `TestSession` works with the `$_SESSION` variable. If you are using something else then you can use the `TestSessionInterface` to create your own.

```php
$this->setSession([
    'user_id' => 1234
]);
```

## Testing Middleware

The `TestRequestHandler` helps making it easy to test middleware in a consistent manner.

```php
public function testMiddleware(): void
{
    $fooMiddleware = new FooMiddleware();
    $serverRequest = new ServerRequest('GET', '/');
    $response = $middleware->process($serverRequest, new TestRequestHandler(new Response()));
    // Do your checks
}
```