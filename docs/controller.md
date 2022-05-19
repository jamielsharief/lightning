# Controller

A PSR-7 `Controller` with `View`,and a couple of important methods `render`, `renderJson` ,`renderFile` and `redirect` to keep code dry when working with `ResponseInterface`.

Create your application controller with the factory method `createResponse`

```php
se Lightning\Controller\AbstractController as BaseController;

class AppController extends BaseController
{
    protected function createResponse(): ResponseInterface
    {
        return new Response(); // A factory method 
    }
}
```

Now create your controllers

```php
class ArticlesController extends AppController
{
    public function index(ServerRequest $request): ResponseInterface
    {
        return $this->render('articles/index', [
            'title' => 'foo',
        ]);
    }
}
```

## Rendering

To a render a `View`

```php
return $this->render('articles/index', [
    'title' => 'foo',
]);
```

To render JSON

```php
return $this->renderJson([
    'title' => 'foo',
]);
```

## Redirecting

To handle redirects

```php
$this->redirect('/articles/view/123'); // or e.g. https://www.example.com
```

## File Response

To render a file in a Response

```php
return $this->renderFile('/var/www/downloads/2021.pdf');
return $this->renderFile('/var/www/downloads/2021.txt',['download' => 'false']); // To not force download
return $this->renderFile('/var/www/downloads/2021.pdf',['name' =>'important.pdf']); // To give the file a different name
```

## PSR 3 Logger


If you provide a PSR-3 `Logger` to the constructor or configure this in your DI container or use the `setLogger` method, then when you call the `log` method you can log to that logger instance.

```php
public function index()
{
    $this->log(LogLevel::DEBUG,'This is a test');
}
```

## PSR 14 Event Dispatcher

If you provide a PSR-14 `EventDispatcher` to the constructor or configure this in your DI container or use the `setEventDispatcher` method, then when you call the `dispatch` method you can dispatch events easily from the controller.


```php
public function thanks(ServerRequestInterface $request)
{
    $this->dispatch(new OrderCompletedEvent($request));
}
```

If the PSR 14 Event Dispatcher is provided, then when the `controller` is created it will dispatch the `InitializeEvent`.