# Controller

A PSR-7 `Controller` with `TemplateRenderer`, and a couple of important methods `render`, `renderJson` ,`renderFile` and `redirect` to keep code dry when working with `ResponseInterface`.

Create your application controller with the factory method `createResponse`

```php
se Lightning\Controller\AbstractController as BaseController;

class AppController extends BaseController
{
    public function createResponse(): ResponseInterface
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

## Callbacks

> The design of this deliberately does not include a specific event implementation e.g. PSR-14 event or Hooks. These methods are provided as the first point of call for getting the desired behavior.

The following callbacks methods are called allowing you modify the behavior of the `Controller`, you can create different versions of the `Controller` using these methods to carry out different actions such as triggering `PSR-14 events` etc or using hooks or quite simply just placing the logic in the methods.

- `initialize`
- `beforeRender`
- `afterRender`
- `beforeRedirect`
- `afterRedirect`

Here is how you could implement `PSR-14` Events using the controller callbacks.

```php
abstract class AbstractEventsController extends AbstractController
{
    protected EventDispatcherInterface $eventDispatcher;

    public function __construct(TemplateRenderer $templateRenderer, EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        parent::__construct($templateRenderer);
        
        $this->eventDispatcher->dispatch(new InitializeEvent($this));
    }

    protected function beforeRender(): ?ResponseInterface
    {
        return $this->eventDispatcher->dispatch(new BeforeRenderEvent($this, $this->request))->getResponse(); // Response object or null
    }

    protected function afterRender(ResponseInterface $response): ResponseInterface
    {
        return $this->eventDispatcher->dispatch(new AfterRenderEvent($this, $this->request, $response))->getResponse();
    }

    protected function beforeRedirect(string $url): ?ResponseInterface
    {
        return $this->eventDispatcher->dispatch(new BeforeRedirectEvent($this, $url, $this->request))->getResponse(); // Response object or null
    }

    protected function afterRedirect(ResponseInterface $response): ResponseInterface
    {
        return $this->eventDispatcher->dispatch(new AfterRedirectEvent($this, $this->request, $response))->getResponse();
    }
}
```