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
use GdImage;

class ViewTest extends TestCase
{
    public function testAttribute(): void
    {
        $route = Route::any('/', #[TestAttribute] fn () => 'conia')->after($this->renderer());
        $view = new View($route, null);

        $this->assertInstanceOf(TestAttribute::class, $view->attributes()[0]);
    }

    public function testClosure(): void
    {
        $route = Route::any('/', fn () => 'conia')->after($this->renderer());
        $view = new View($route, null);

        $this->assertEquals('conia', (string)$view->execute($this->request())->getBody());
    }

    public function testFunction(): void
    {
        $route = Route::any('/{name}', 'testViewWithAttribute')->after($this->renderer());
        $route->match('/symbolic');
        $view = new View($route, null);

        $this->assertEquals('symbolic', (string)$view->execute($this->request())->getBody());
        $this->assertInstanceOf(TestAttribute::class, $view->attributes()[0]);
    }

    public function testControllerString(): void
    {
        $route = Route::any('/', '\Conia\Route\Tests\Fixtures\TestController::textView')->after($this->renderer());
        $view = new View($route, null);

        $this->assertEquals('text', (string)$view->execute($this->request())->getBody());
        $this->assertInstanceOf(TestAttribute::class, $view->attributes()[0]);
    }

    public function testControllerClassMethod(): void
    {
        $route = Route::any('/', [TestController::class, 'textView'])->after($this->renderer());
        $view = new View($route, null);

        $this->assertEquals('text', (string)$view->execute($this->request())->getBody());
        $this->assertInstanceOf(TestAttribute::class, $view->attributes()[0]);
    }

    public function testControllerObjectMethod(): void
    {
        $controller = new TestController();
        $route = Route::any('/', [$controller, 'textView'])->after($this->renderer());
        $view = new View($route, null);

        $this->assertEquals('text', (string)$view->execute($this->request())->getBody());
        $this->assertInstanceOf(TestAttribute::class, $view->attributes()[0]);
    }

    public function testInvokableClass(): void
    {
        $route = Route::any('/', 'Conia\Route\Tests\Fixtures\TestInvokableClass')->after($this->renderer());
        $view = new View($route, null);

        $this->assertEquals('Invokable', (string)$view->execute($this->request())->getBody());
    }

    public function testNonexistentControllerView(): void
    {
        $this->throws(RuntimeException::class, 'View method not found');

        $route = Route::any('/', TestController::class . '::nonexistentView')->after($this->renderer());
        $view = new View($route, null);
        $view->execute($this->request());
    }

    public function testNonexistentController(): void
    {
        $this->throws(RuntimeException::class, 'Controller not found');

        $route = Route::any('/', NonexisitentTestController::class . '::nonexistentView')->after($this->renderer());
        $view = new View($route, null);
        $view->execute($this->request());
    }

    public function testControllerWithRequestInConstructor(): void
    {
        $request = $this->request();
        $route = Route::any('/', TestControllerWithRequest::class . '::requestOnly')->after($this->renderer());
        $view = new View($route, null);

        $this->assertEquals($request::class, (string)$view->execute($request)->getBody());
    }

    public function testControllerWithRouteInConstructor(): void
    {
        $route = Route::any('/', TestControllerWithRoute::class . '::routeOnly')->after($this->renderer());
        $view = new View($route, null);

        $this->assertEquals($route::class, (string)$view->execute($this->request())->getBody());
    }

    public function testControllerWithRequestRouteAndParamInConstructor(): void
    {
        $request = $this->request();
        $route = Route::any(
            '/{param}',
            TestControllerWithRequestAndRoute::class . '::requestAndRoute'
        )->after($this->renderer());
        $route->match('/conia');
        $view = new View($route, null);

        $this->assertEquals(
            $request::class . $route::class . 'conia',
            (string)$view->execute($request)->getBody()
        );
    }

    public function testViewWithRouteParams(): void
    {
        $request = $this->request();
        $route = Route::any(
            '/{string}/{float}-{int}',
            TestControllerWithRequest::class . '::routeParams'
        )->after($this->renderer());
        $route->match('/symbolic/7.13-23');
        $view = new View($route, null);

        $this->assertEquals(
            '{"string":"symbolic","float":7.13,"int":23,"request":"Laminas\\\\Diactoros\\\\ServerRequest"}',
            (string)$view->execute($request)->getBody()
        );
    }

    public function testViewWithDefaultValueParams(): void
    {
        // Should overwrite the default value
        $route = Route::any(
            '/{string}/{int}',
            TestController::class . '::routeDefaultValueParams'
        )->after($this->renderer());
        $route->match('/symbolic/17');
        $view = new View($route, null);

        $this->assertEquals('{"string":"symbolic","int":17}', (string)$view->execute($this->request())->getBody());

        // Should use the default value
        $route = Route::any('/{string}', TestController::class . '::routeDefaultValueParams')->after($this->renderer());
        $route->match('/symbolic');
        $view = new View($route, null);

        $this->assertEquals('{"string":"symbolic","int":13}', (string)$view->execute($this->request())->getBody());
    }

    public function testViewWithWrongRouteParams(): void
    {
        $this->throws(RuntimeException::class, 'cannot be resolved');

        $route = Route::any(
            '/{wrong}/{param}',
            TestControllerWithRequest::class . '::routeParams'
        )->after($this->renderer());
        $route->match('/symbolic/test');
        $view = new View($route, null);
        $view->execute($this->request());
    }

    public function testViewWithWrongTypeForIntParam(): void
    {
        $this->throws(RuntimeException::class, "Cannot cast 'int' to int");

        $route = Route::any(
            '/{string}/{float}-{int}',
            TestControllerWithRequest::class . '::routeParams'
        )->after($this->renderer());
        $route->match('/symbolic/7.13-wrong');
        $view = new View($route, null);
        $view->execute($this->request());
    }

    public function testViewWithWrongTypeForFloatParam(): void
    {
        $this->throws(RuntimeException::class, "Cannot cast 'float' to float");

        $route = Route::any(
            '/{string}/{float}-{int}',
            TestControllerWithRequest::class . '::routeParams'
        )->after($this->renderer());
        $route->match('/symbolic/wrong-13');
        $view = new View($route, null);
        $view->execute($this->request());
    }

    public function testViewWithUnsupportedParam(): void
    {
        $this->throws(RuntimeException::class, 'Unresolvable: GdImage');

        $route = Route::any('/{name}', fn (GdImage $name) => $name)->after($this->renderer());
        $route->match('/symbolic');
        $view = new View($route, null);
        $view->execute($this->request());
    }

    public function testAttributeFilteringCallableView(): void
    {
        $route = Route::any(
            '/',
            #[TestAttribute, TestAttributeExt, TestAttributeDiff]
            fn () => 'conia'
        )->after($this->renderer());
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
