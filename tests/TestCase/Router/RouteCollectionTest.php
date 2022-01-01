<?php declare(strict_types=1);

namespace Lightning\Test\Router;

use PHPUnit\Framework\TestCase;
use Lightning\Router\RouteCollection;

final class RouteCollectionTest extends TestCase
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
    public function testMethod(string $method, string $expectedMethod): void
    {
        $routes = new RouteCollection();

        $route = $routes->$method('/articles/action/:id', 'App\Controller\ArticlesController::action');

        $this->assertEquals($expectedMethod, $route->getMethod());
        $this->assertEquals('/articles/action/:id', $route->getPath());
        $this->assertEmpty($route->getConstraints());
    }

    public function testMap(): void
    {
        $routes = new RouteCollection();

        $route = $routes->map('PUT', '/articles/action/:id', 'App\Controller\ArticlesController::action', []);

        $this->assertEquals('PUT', $route->getMethod());
        $this->assertEquals('/articles/action/:id', $route->getPath());
        $this->assertEmpty($route->getConstraints());
    }

    public function testMatchPrefix(): void
    {
        $group = new RouteCollection('/admin', function () {
        });
        $this->assertTrue($group->matchPrefix('/admin'));
        $this->assertTrue($group->matchPrefix('/admin/'));
        $this->assertTrue($group->matchPrefix('/admin/login'));
        $this->assertFalse($group->matchPrefix('/admin-shoes'));
    }

    public function testGetRoutesConfig(): void
    {
        $group = new RouteCollection('/admin', function (RouteCollection $routes) {
            $routes->get('/login', 'App\Controller\Admin::login');
            $routes->get('/logout', 'App\Controller\Admin::logout');
        });

        $this->assertCount(2, $group->getRoutes());
    }

    public function testRouteCollectionHavePrefix(): void
    {
        $group = new RouteCollection('/admin', function (RouteCollection $routes) {
            $routes->get('/login', 'App\Controller\Admin::login');
        });

        $this->assertEquals('/admin/login', $group->getRoutes()[0]->getPath());
    }
}
