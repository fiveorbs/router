<?php

declare(strict_types=1);

namespace Conia\Route\Tests;

use Conia\Route\Exception\RuntimeException;
use Conia\Route\Route;
use Conia\Route\Tests\Fixtures\TestAttribute;
use Conia\Route\Tests\Fixtures\TestAttributeDiff;
use Conia\Route\Tests\Fixtures\TestAttributeExt;
use Conia\Route\Tests\Fixtures\TestController;
use Conia\Route\Tests\Fixtures\TestControllerWithRequest;
use Conia\Route\Tests\Fixtures\TestControllerWithRequestAndRoute;
use Conia\Route\Tests\Fixtures\TestControllerWithRoute;
use Conia\Route\View;

class ViewTest extends TestCase
{
    public function testAttribute(): void
    {
        $route = new Route('/', #[TestAttribute] fn () => 'conia');
        $view = new View($route, null);

        $this->assertInstanceOf(TestAttribute::class, $view->attributes()[0]);
    }

    #
    public function testClosure(): void
    {
        $route = new Route('/', fn () => 'conia');
        $view = new View($route, null);

        $this->assertEquals('conia', $view->execute($this->request()));
    }

    public function testFunction(): void
    {
        $route = new Route('/{name}', 'testViewWithAttribute');
        $route->match('/symbolic');
        $view = new View($route, null);

        $this->assertEquals('symbolic', $view->execute($this->request()));
        $this->assertInstanceOf(TestAttribute::class, $view->attributes()[0]);
    }

    public function testControllerString(): void
    {
        $route = new Route('/', '\Conia\Route\Tests\Fixtures\TestController::textView');
        $view = new View($route, null);

        $this->assertEquals('text', $view->execute($this->request()));
        $this->assertInstanceOf(TestAttribute::class, $view->attributes()[0]);
    }

    public function testControllerClassMethod(): void
    {
        $route = new Route('/', [TestController::class, 'textView']);
        $view = new View($route, null);

        $this->assertEquals('text', $view->execute($this->request()));
        $this->assertInstanceOf(TestAttribute::class, $view->attributes()[0]);
    }

    public function testControllerObjectMethod(): void
    {
        $controller = new TestController();
        $route = new Route('/', [$controller, 'textView']);
        $view = new View($route, null);

        $this->assertEquals('text', $view->execute($this->request()));
        $this->assertInstanceOf(TestAttribute::class, $view->attributes()[0]);
    }

    public function testInvokableClass(): void
    {
        $route = new Route('/', 'Conia\Route\Tests\Fixtures\TestInvokableClass');
        $view = new View($route, null);

        $this->assertEquals('Invokable', $view->execute($this->request()));
    }

    public function testNonexistentControllerView(): void
    {
        $this->throws(RuntimeException::class, 'View method not found');

        $route = new Route('/', TestController::class . '::nonexistentView');
        $view = new View($route, null);
        $view->execute($this->request());
    }

    public function testNonexistentController(): void
    {
        $this->throws(RuntimeException::class, 'Controller not found');

        $route = new Route('/', NonexisitentTestController::class . '::nonexistentView');
        $view = new View($route, null);
        $view->execute($this->request());
    }

    public function testControllerWithRequestInConstructor(): void
    {
        $request = $this->request();
        $route = new Route('/', TestControllerWithRequest::class . '::requestOnly');
        $view = new View($route, null);

        $this->assertEquals($request, $view->execute($request));
    }

    public function testControllerWithRouteInConstructor(): void
    {
        $route = new Route('/', TestControllerWithRoute::class . '::routeOnly');
        $view = new View($route, null);

        $this->assertEquals($route, $view->execute($this->request()));
    }

    public function testControllerWithRequestRouteAndParamInConstructor(): void
    {
        $request = $this->request();
        $route = new Route('/{param}', TestControllerWithRequestAndRoute::class . '::requestAndRoute');
        $route->match('/conia');
        $view = new View($route, null);

        $this->assertEquals([$request, $route, 'conia'], $view->execute($request));
    }

    public function testViewWithRouteParams(): void
    {
        $request = $this->request();
        $route = new Route('/{string}/{float}-{int}', TestControllerWithRequest::class . '::routeParams');
        $route->match('/symbolic/7.13-23');
        $view = new View($route, null);

        $this->assertEquals(
            [
                'string' => 'symbolic',
                'float' => 7.13,
                'int' => 23,
                'request' => $request::class,
            ],
            $view->execute($request)
        );
    }

    public function testDispatchViewWithDefaultValueParams(): void
    {
        // Should overwrite the default value
        $route = new Route(
            '/{string}/{int}',
            TestController::class . '::routeDefaultValueParams'
        );
        $route->match('/symbolic/17');
        $view = new View($route, null);

        $this->assertEquals([
            'string' => 'symbolic',
            'int' => 17,
        ], $view->execute($this->request()));

        // Should use the default value
        $route = new Route('/{string}', TestController::class . '::routeDefaultValueParams');
        $route->match('/symbolic');
        $view = new View($route, null);

        $this->assertEquals([
            'string' => 'symbolic',
            'int' => 13,
        ], $view->execute($this->request()));
    }

    public function testAttributeFilteringCallableView(): void
    {
        $route = new Route('/', #[TestAttribute, TestAttributeExt, TestAttributeDiff] fn () => 'conia');
        $view = new View($route, null);

        $this->assertEquals(3, count($view->attributes()));
        $this->assertEquals(2, count($view->attributes(TestAttribute::class)));
        $this->assertEquals(1, count($view->attributes(TestAttributeExt::class)));
        $this->assertEquals(1, count($view->attributes(TestAttributeDiff::class)));
    }

    public function testAttributeFilteringControllerView(): void
    {
        $route = new Route('/', [TestController::class, 'arrayView']);
        $view = new View($route, null);

        $this->assertEquals(3, count($view->attributes()));
        $this->assertEquals(2, count($view->attributes(TestAttribute::class)));
        $this->assertEquals(1, count($view->attributes(TestAttributeExt::class)));
        $this->assertEquals(1, count($view->attributes(TestAttributeDiff::class)));
    }
}
