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
- BeforeRender - Psr\EventDispatcher\StoppableEventInterface
- BeforeRedirect - Psr\EventDispatcher\StoppableEventInterface
- AfterRender

If you want to change the response in the event, then make sure to update the `Controller` since responses are immutable.
