# Controller

A PSR-7 `Controller` with `View`,and a couple of important methods `render`, `renderJson` ,`renderFile` and `redirect` to keep code dry when working with `ResponseInterface`.

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


## Router & PSR-14 implementaion Example

If you are using the lightning router, you can add hook the beforeFilter and afterFilter events.


```php
class AppController extends AbstractController implements EventSubscriberInterface
{
    protected ?string $layout = 'default';

    public function getSubscribedEvents(): array
    {
        return [
            BeforeFilterEvent::class => 'beforeFilter',
            AfterFilterEvent::class => 'afterFilter'
        ];
    }
    public function __construct(View $view, protected EventDispatcher $eventDispatcher)
    {
        $eventDispatcher->addSubscriber($this);

        parent::__construct($view);
    }

    public function beforeFilter(BeforeFilterEvent $event): void
    {
    }

    public function afterFilter(AfterFilterEvent $event): void
    {
    }

    public function createResponse(): ResponseInterface
    {
        return new Response();
    }
}
```
