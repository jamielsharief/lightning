<?php declare(strict_types=1);

namespace Lightning\Test\Router;

use Nyholm\Psr7\Response;
use Lightning\Router\Route;
use Lightning\Router\Router;
use Lightning\Cache\ApcuCache;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Lightning\Autowire\Autowire;
use Lightning\Router\RouteCollection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;

use Psr\Http\Server\RequestHandlerInterface;
use Lightning\Router\Exception\RouterException;

class DummyController
{
    public function index()
    {
        return new Response(200, [], 'ok');
    }

    public function autowire(ServerRequestInterface $request, ResponseInterface $response, ApcuCache $class)
    {
        return new Response(200, [], 'ok');
    }
}

class SingleActionController
{

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        return new Response(200, [], 'ok');
    }
}

class BaseTestMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request);
    }
}

class CaramelCornMiddleware extends BaseTestMiddleware
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $response->getBody()->write('caramel');

        return $response;
    }
}

class LemonLimeMiddleware extends BaseTestMiddleware
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $response->getBody()->write('lemon');

        return $response;
    }
}

class GreenAppleMiddleware extends BaseTestMiddleware
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $response->getBody()->write('apple');

        return $response;
    }
}

// TODO: test attributes need to be added back
final class RouterTest extends TestCase
{
    public function methodProvider()
    {
        return [
            ['get','GET'],
            ['post','POST'],
            ['put','PUT'],
            ['patch','PATCH'],
            ['delete','DELETE'],
            ['head','HEAD'],
            ['options','OPTIONS']
        ];
    }

    /**
     * @dataProvider methodProvider
     */
    public function testRequestMethod(string $method, string $expectedMethod): void
    {
        $route = (new Router())->$method('/articles/action/:id', 'App\Controller\ArticlesController::action');
        $this->assertEquals($expectedMethod, $route->getMethod());
        $this->assertEquals('/articles/action/:id', $route->getPath());
        $this->assertEmpty($route->getConstraints());
    }

    /**
    * @dataProvider methodProvider
    */
    public function testRequestMethodWithConstraints(string $method, string $expectedMethod): void
    {
        $constraints = ['id' => '/^[0-9]+/i'];
        $route = (new Router())->$method('/articles/action/:id', 'App\Controller\ArticlesController::action', $constraints);
        $this->assertEquals($expectedMethod, $route->getMethod());
        $this->assertEquals('/articles/action/:id', $route->getPath());
        $this->assertSame($constraints, $route->getConstraints());
    }

    public function testMatchCurrent(): void
    {
        $router = new Router();
        $router->get('/articles', [DummyController::class,'index']);

        $route = $router->match(new ServerRequest('GET', 'http://localhost/articles'));

        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals('/articles', $route->getPath());
    }

    public function testNoMatch(): void
    {
        $router = new Router();
        $router->get('/articles', [DummyController::class,'index']);

        $this->assertNull(
            $router->match(new ServerRequest('GET', 'http://localhost/login'))
        );
    }

    public function testMatchWithMiddleware(): void
    {
        $router = new Router();
        $router->middleware(new GreenAppleMiddleware());
        $router->get('/articles', [DummyController::class,'index']);

        $route = $router->match(new ServerRequest('GET', 'http://localhost/articles'));

        $this->assertEquals('/articles', $route->getPath());
        $this->assertCount(1, $route->getMiddlewares());
    }

    public function testMatchWithMiddlewares(): void
    {
        $router = new Router();

        $router->middleware(new GreenAppleMiddleware());
        $router->get('/articles', [DummyController::class,'index'])->middleware(new LemonLimeMiddleware());

        $route = $router->match(new ServerRequest('GET', 'http://localhost/articles'));

        $this->assertEquals('/articles', $route->getPath());

        $this->assertCount(2, $route->getMiddlewares());
    }

    public function testGroup(): void
    {
        $router = new Router();

        $router->group('/admin', function (RouteCollection $routes) {
            $routes->get('/login', [DummyController::class,'index']);
        });

        $route = $router->match(new ServerRequest('GET', 'http://localhost/admin/login'));
        $this->assertEquals('/admin/login', $route->getPath());
        $this->assertCount(0, $route->getMiddlewares());
    }

    public function testGroupNoMatch(): void
    {
        $router = new Router();
        $router->group('/admin', function (RouteCollection $routes) {
            $routes->get('/login', [DummyController::class,'index']);
        });

        $this->assertNull(
            $router->match(new ServerRequest('GET', 'http://localhost/login'))
        );;
    }

    public function testGroupWithMiddleware(): void
    {
        $router = new Router();

        $router->middleware(new GreenAppleMiddleware());
        $router->group('/admin', function (RouteCollection $routes) {
            $routes->get('/login', [DummyController::class,'index']);
        });
        $route = $router->match(new ServerRequest('GET', 'http://localhost/admin/login'));
        $this->assertEquals('/admin/login', $route->getPath());
        $this->assertCount(1, $route->getMiddlewares());
    }

    public function testGroupWithMiddlewares(): void
    {
        $router = new Router();

        $router->middleware(new GreenAppleMiddleware());
        $router->group('/admin', function (RouteCollection $routes) {
            $routes->get('/login', [DummyController::class,'index']);
        })->middleware(new LemonLimeMiddleware());

        $route = $router->match(new ServerRequest('GET', 'http://localhost/admin/login'));
        $this->assertEquals('/admin/login', $route->getPath());
        $this->assertCount(2, $route->getMiddlewares());
    }

    public function testGroupWithMoreMiddlewares(): void
    {
        $router = new Router();

        $router->middleware(new GreenAppleMiddleware());
        $router->group('/admin', function (RouteCollection $routes) {
            $routes->get('/login', [DummyController::class,'index'])->middleware(new CaramelCornMiddleware());
        })->middleware(new LemonLimeMiddleware());

        $route = $router->match(new ServerRequest('GET', 'http://localhost/admin/login'));
        $this->assertEquals('/admin/login', $route->getPath());
        $this->assertCount(3, $route->getMiddlewares());
    }

    // public function testParseRequest(): void
    // {
    //     $router = new Router();
    //     $router->get('/articles', ['Lightning\Test\Router\DummyController','index']);

    //     $route = $router->parseRequest(new ServerRequest('GET', '/articles'));

    //     $this->assertInstanceOf(Route::class, $route);
    //     $this->assertEquals('/articles', $route->getPath());
    // }

    public function testDispatch(): void
    {
        $router = new Router();
        $router->get('/articles', ['Lightning\Test\Router\DummyController','index']);

        $response = $router->handle(new ServerRequest('GET', '/articles'));
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testDispatchWithMiddlewares(): void
    {
        $router = new Router();
        $router->get('/articles', ['Lightning\Test\Router\DummyController','index'])
            ->middleware(new GreenAppleMiddleware())
            ->middleware(new LemonLimeMiddleware());
        $response = $router->dispatch(new ServerRequest('GET', '/articles'));
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertStringContainsString('oklemonapple', $response->getBody()->__toString());
    }

    public function testNotResolvable(): void
    {
        $router = new Router();
        $router->get('/articles', 'FunkyController::action');

        $this->expectException(RouterException::class);
        $this->expectExceptionMessage('Error resolving `FunkyController`');
        $router->match(new ServerRequest('GET', '/articles'));
    }

    public function testNotCallable(): void
    {
        $router = new Router();
        $router->get('/articles', [$this,'foo']);

        $this->expectException(RouterException::class);
        $this->expectExceptionMessage('The handler for `GET /articles` is not a callable');
        $router->match(new ServerRequest('GET', '/articles'));
    }

    public function testNoResponse(): void
    {
        $router = new Router();
        $router->get('/articles', function () {
        });

        $this->expectException(RouterException::class);
        $this->expectExceptionMessage('No response was returned');

        $router->dispatch(new ServerRequest('GET', '/articles'));
    }

    // Test with ResponseObject params as well
    public function testAutowireMethod(): void
    {
        $router = new Router(null, new Autowire(), new Response());
        $router->get('/articles', ['Lightning\Test\Router\DummyController','autowire']);

        $response = $router->dispatch(new ServerRequest('GET', '/articles'));
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testAutowireFunction(): void
    {
        $router = new Router(null, new Autowire(), new Response());
        $router->get('/articles', function (ServerRequestInterface $request, ResponseInterface $response, ApcuCache $class) {
            return new Response(200, [], 'ok');
        });

        $response = $router->dispatch(new ServerRequest('GET', '/articles'));
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }


    public function testAutowireActionController(): void
    {
        $router = new Router(null, new Autowire(), new Response());
        $router->get('/articles', SingleActionController::class);

        $response = $router->dispatch(new ServerRequest('GET', '/articles'));
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testVariablesWereAdded(): void
    {
        $router = new Router();
        $router->get('/foo/:id', function (ServerRequestInterface $request) {
            $this->assertEquals('1234', $request->getAttribute('id'));

            return new Response();
        }, ['id' => '[0-9]+']);

        $response = $router->handle(new ServerRequest('GET', '/foo/1234'));
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }
}
