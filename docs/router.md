# Lightning Router

A lightweight PSR-7 and PSR-15 router with support for PSR-11, PSR-14.

## Usage

Create the `Router` object then dispatch with the `ServerRequestInterface` request.

```php
$router = new Router();
$router->get('/articles/index', function (ServerRequestInterface $request) {
    $response = new Response();
    $response->getBody()->write(json_encode(['foo'=>'bar']));
    return $response->withStatus(200);
});
$router->dispatch($request); // Psr\Http\Message\ServerRequestInterface
```

To configure a route to use a `callable`

```php
$router->get('/articles', [new ArticlesController, 'index']);
$router->get('/articles/home', ArticlesHomeController::class);
```

You can also use proxies which will create the `callable`, and if you created the router with a `PSR-11 Container` then it will use this to create the object, if its available in `Container`.

```php
$router->get('/articles/index', 'App\Controller\ArticlesController::index');
$router->get('/articles/index', [ArticlesController::class, 'index']);
$router->get('/articles/index', ArticlesIndexController::class); // __invoke method
```

## Method arguments

By default the `ServerRequestInterface` object will be passed to the `callable`, and if you created the `Router` with a blank `ResponseInterface` object this will be passed as a second argument.

```php
class ArticlesController
{
    public function show(ServerRequestInterface $request) : ResponseInterface
    {
        $id = (int) $request->getAttribute('id');

        $response = new Response();
        $response->getBody()->write("<h1>Articles <small>{$id}</small></h1>");
        return $response->withStatus(200);
    }
}
```

## URL Variables

When you need to get a value from the URL

```php
$router->get('/articles/:id', 'App\Controller\ArticlesController::show');
```

This will be added to the ``ServerRequestInterface` object.

```php
$id = (int) $request->getAttribute('id')
```

For security you can make sure that the data that is being passed matches a regular expression pattern. Note: `#^`and `$#` will be added automatically.

```php
$router->delete('/articles/:id', 'App\Controller\ArticlesController::delete',[
    'id' => '[0-9]{1,20}'
]);
```

## Route Groups

You can group your route definitions together, these routes will only be processed if there is a match on the prefix.

```php
$router->group('/admin', function (RoutesInterface $routes) {
    $routes->get('/dashboard', 'App\Controller\AdminController::dashboard'); // GET /admin/dashboard
});
```

## Middleware

To add `Middleware` on all routes

```php
$router->middleware(new FooMiddleware);
```

To add a `Middleware` to the start of the queue

```php
$router->prependMiddleware(new FooMiddleware);
```

To add for an individual `Route`

```php
$router->get('/articles', [new ArticlesController,'index'])->middleware(new AuthMiddleware);
```

To add `Middleware` for all `Routes` in a group.

```php
$router->group('/admin', function (RoutesInterface $routes) {
    $routes->get('/dashboard', 'App\Controller\AdminController::dashboard'); // GET /admin/dashboard
})->middleware(new AuthMiddleware);
```

## Resources

Here is an example for reference on a sample configuration

```php
$router->get('/articles/new', [ArticlesController::class,'new']);
$router->get('articles', [ArticlesController::class,'index']);
$router->post('articles', [ArticlesController::class,'create']);
$router->get('/articles/:id/edit', [ArticlesController::class,'edit'], ['id' => '[0-9]+']);
$router->get('/articles/:id', [ArticlesController::class,'show'], ['id' => '[0-9]+']);
$router->patch('/articles/:id', [ArticlesController::class,'update'], ['id' => '[0-9]+']);
$router->delete('/articles/:id', [ArticlesController::class,'destroy'], ['id' => '[0-9]+']);
```

## PSR-11: DI Container

When creating the Router object add a `Container` object to use when creating the object from the matched route proxy.

## Autowiring

You can also use autowiring of methods or closures, simply supply the `Autowire` object when creating the Router object.


## ControllerInterface

You can add the `ControllerInterface` add life cycle callbacks to your `Controller` which will be called before and after the action is invoked.

```php
class ArticlesController implements ControllerInterface
{
    public function beforeFilter(ServerRequestInterface $request): ?ResponseInterface
    {
        $this->request = $request;
        return $this->eventDispatcher->dispatch(new BeforeFilterEvent($request))->getResponse(); // Response object or null
    }

    public function afterFilter(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
       return $this->eventDispatcher->dispatch(new AfterFilterEvent($request, $response))->getResponse(); // Response object
    }
}
```