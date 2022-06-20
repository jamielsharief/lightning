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

## Life Cycle Callbacks

The `Controller` comes with the following hooks to allow you modify the behavior of the `Controller`

- `initialize`
- `beforeRender`
- `afterRender`
- `beforeRedirect`
- `afterRedirect`

Here is how you could implement `PSR-14` Events using the controller callbacks.

```php
abstract class AbstractPsrEventsController extends AbstractController
{
    protected EventDispatcherInterface $eventDispatcher;

    public function __construct(TemplateRenderer $templateRenderer, EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        parent::__construct($templateRenderer);
        
        $this->eventDispatcher->dispatch(new InitializeEvent($this));
    }

    public function beforeRender(): ?ResponseInterface
    {
        return $this->eventDispatcher->dispatch(new BeforeRenderEvent($this, $this->request))->getResponse(); // response or null
    }

    public function afterRender(ResponseInterface $response): ResponseInterface
    {
        return $this->eventDispatcher->dispatch(new AfterRenderEvent($this, $this->request, $response))->getResponse();
    }

    public function beforeRedirect(string $url): ?ResponseInterface
    {
        return $this->eventDispatcher->dispatch(new BeforeRedirectEvent($this, $url, $this->request))->getResponse(); // response or null
    }

    public function afterRedirect(ResponseInterface $response): ResponseInterface
    {
        return $this->eventDispatcher->dispatch(new AfterRedirectEvent($this, $this->request, $response))->getResponse();
    }
}
```