# Controller

A PSR friendly `Controller` with `View`, with a couple of important methods `render`, `renderJson` ,`renderFile` and `redirect` to keep code dry when working with `ResponseInterface` with events.

Also works with  PSR-14 event dispatcher and PSR-3 logger.

```php
use Lightning\Controller\AbstractController as BaseController;

class ArticlesController extends BaseController
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

## Events

When the `Controller` is created will run the `initialize` method so that you do not have to override the constructor.

The following PSR events are triggered

- AfterInitialize
- BeforeFilter - Psr\EventDispatcher\StoppableEventInterface
- BeforeRender - Psr\EventDispatcher\StoppableEventInterface
- BeforeRedirect - Psr\EventDispatcher\StoppableEventInterface
- AfterRender
- AfterFilter

> The `beforeFilter` and `afterFilter` events are dispatched when the controller `startup` and `shutdown` methods are called, which are called by the Lightning router, you can use a different router but you will need to call those methods to use this events and hooks.

If you want to change a `Response` during an `Event` set the `Response` object in `Event`, stopping the event does not change the response nor does setting the `Response` in the `Controller`.


## Hooks

The `Controller` uses the `Hook` component, this allows you to hook into the controller and change its behavior.

The following Hooks can be triggered

- AfterInitialize
- BeforeFilter - Stoppable
- BeforeRender - Stoppable
- BeforeRedirect - Stoppable
- AfterRender
- AfterFilter

> The `beforeFilter` and `afterFilter` hooks are triggered when the controller `startup` and `shutdown` methods are called, which are called by the Lightning router, you can use a different router but you will need to call those methods to use this events and hooks.

If you return `false` in the stoppable hooks, the `Controller` response will be returned, likewise if in those methods you change the `Controller` response to a redirect, this will stop the processing and return that response.


In your `Controller` you can register the hook

```php
protected function initialize() : void
{
    $this->registerHook('beforeFilter','doSomething');
}

public function doSomething(ServerRequestInterface $request) : bool 
{
    // do something here
    return true;
}

```

You can also create reusuable behaviors using traits.

```php
trait DoSomething
{
    public function initializeDoSomethingTrait(): void 
    {
        $this->registerHook('beforeFilter','doSomething');
    }

    public function doSomething(ServerRequestInterface $request) : bool 
    {
        // do something here
        return true;
    }
}
```