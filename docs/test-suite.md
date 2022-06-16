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

## Testing Dispatched Events

### TestEventDispatcher

The `TestEventDispatcher` is PSR 14 Event Dispatcher which is for testing wether `Events` where triggered or not.

It comes with the additional methods

```php
$eventDispatcher = new TestEventDispatcher(new EventDispatcher);

$events = $eventDispatcher->getDispatchedEvents(); // [BeforeFind::class]
$event = $eventDispatcher->getDispatchedEvent(BeforeFind::class);
$bool = $eventDispatcher->hasDispatchedEvent(BeforeFind::class);
```

You can call 

```php
$eventDispatcher->on(BeforeFind::class, function(BeforeFind $event){
    $event->stop();
})
```

### EventDispatcherTestTrait

The `EventDispatcherTestTrait` makes easier to test dispatched events in applications.

```php
use EventDispatcherTestTraitTestTrait;

public function setUp(): void 
{
    $dispatcher = $this->createEventDispatcher();
    $this->setEventDispatcher($dispatcher);
}

public function testDoSomething(): void 
{
    $object = new SomeObject($this->getEventDispatcher());
    $object->doSomething();
    $this->assertEventDispatched(BeforeSomething::class);
}
```

The following methods are provided:

```php
$this->assertEventsDispatchedCount(5);

$this->assertEventDispatched(BeforeSomething::class);
$this->assertEventNotDispatched(BeforeSomething::class);

// These check that events were or were not dispatched (regardless of order or other events being dispatched)
$this->assertEventsDispatched([BeforeSomething::class]);
$this->assertEventsNotDispatched([BeforeSomething::class]);

// These check that only these events were/were not dispatched in this order
$this->assertEventsDispatchedEquals([BeforeSomething::class]); 
$this->assertEventsDispatchedNotEquals([BeforeSomething::class]);
```

## Testing Logging

### TestLogger

The `TestLogger` is a PSR logger event dispatcher which is for testing.

It comes with two additional methods

```php
// Checks an exact message is in the log
$bool = $testLogger->hasMessage('Invoice #355 was printed', LogLevel::DEBUG);
// Checks the unrendered version
$bool = $testLogger->hasMessage('Invoice #{number} was printed', LogLevel::DEBUG, false)
// Check for a partial string in the log
$bool = $testLogger->hasMessageThatContains('could not send email', LogLevel::ERROR);
count($testLogger); // count the logged items
// Check using regex
$bool = $testLogger->hasMessageThatMatches('/could not send (sms|email)/', LogLevel::ERROR);
count($testLogger); // count the logged items
```

### LoggerTestTrait

The `LoggerTestTrait` provides various assertation methods, making it easier to test applications.

```php
use LoggerTestTrait;

public function setUp(): void 
{
    $testLogger = $this->createLogger();
    $this->setLogger($testLogger);
}

public function testDoSomething(): void 
{
    $object = new SomeObject($this->getLogger());
    $object->doSomething();
    
    $this->assertLogHasMessage('Could not do something', LogLevel::ERROR);
}
```

The following methods are provided:

```php
$this->assertLogHasMessage('Could not connect to SMTP server', LogLevel::ERROR);
$this->assertLogHasMessageThatContains('SMTP server', LogLevel::ERROR);
$this->assertLogHasMessageThatMatches('Error sending (email|sms)', LogLevel::ERROR);

$this->assertLogDoesNotHaveMessage('Could not connect to SMTP server', LogLevel::ERROR);
$this->assertLogDoesNotHaveMessageThatContains('SMTP server', LogLevel::ERROR);
$this->assertLogDoesNotHaveMessageThatMatches('Error sending (email|sms)', LogLevel::ERROR);

$this->assertLogMessagesCount(5);
```

## Testing Middleware

The `TestRequestHandler` helps making it easy to test middleware in a consistent manner.

```php
public function testMiddleware(): void
{
    $handler = new TestRequestHandler(new FooMiddleware(), new Response())
    $response = $handler->dispatch( new ServerRequest('GET', '/'));
    // Do your checks
}
```

There is also a `beforeHandle` method which accepts a `callback`, here you can do prechecks

```php
$handler = new TestRequestHandler(new FooMiddleware(), new Response())
$handler->beforeHandle(function(ServerRequestInterface $request) use ($object){
    $this->assertTrue($object->wasCalled());
});
$handler->dispatch(new ServerRequest('GET', '/'));
```

You can also get the `ServerRequestInterface` object that was passed to the `handle` method, this is useful in situations where `Middleware` is modifying the request object.

```php
$handler->dispatch(new ServerRequest('GET', '/'));
$this->assertEquals('bar',$handler->getRequest()->getAttribute('foo'));
```