<?php

declare(strict_types=1);

namespace Conia\Route\Tests;

use Conia\Registry\Exception\ContainerException;
use Conia\Route\Route;
use Conia\Route\Tests\Fixtures\TestAttribute;
use Conia\Route\Tests\Fixtures\TestAttributeDiff;
use Conia\Route\Tests\Fixtures\TestAttributeExt;
use Conia\Route\Tests\Fixtures\TestController;
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
