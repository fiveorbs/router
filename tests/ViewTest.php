<?php

declare(strict_types=1);

namespace Conia\Route\Tests;

use Conia\Registry\Exception\ContainerException;
use Conia\Registry\Registry;
use Conia\Route\Renderer\Renderer;
use Conia\Route\Route;
use Conia\Route\Tests\Fixtures\TestAttribute;
use Conia\Route\Tests\Fixtures\TestAttributeDiff;
use Conia\Route\Tests\Fixtures\TestAttributeExt;
use Conia\Route\Tests\Fixtures\TestController;
use Conia\Route\Tests\Fixtures\TestRendererArgsOptions;
use Conia\Route\View;
use ReflectionFunction;
use ReflectionMethod;

class ViewTest extends TestCase
{
    public function testClosure(): void
    {
        $route = new Route('/', #[TestAttribute] fn () => 'chuck');
        $route->match('/');
        $view = new View($route->view(), $route->args(), $this->registry());

        $this->assertEquals('chuck', $view->execute());
        $this->assertInstanceOf(TestAttribute::class, $view->attributes()[0]);
    }

    public function testFunction(): void
    {
        $route = new Route('/{name}', 'testViewWithAttribute');
        $route->match('/symbolic');
        $view = new View($route->view(), $route->args(), $this->registry());

        $this->assertEquals('symbolic', $view->execute());
        $this->assertInstanceOf(TestAttribute::class, $view->attributes()[0]);
    }

    public function testControllerString(): void
    {
        $route = new Route('/', '\Conia\Route\Tests\Fixtures\TestController::textView');
        $route->match('/');
        $view = new View($route->view(), $route->args(), $this->registry());

        $this->assertEquals('text', $view->execute());
        $this->assertInstanceOf(TestAttribute::class, $view->attributes()[0]);
    }

    public function testControllerClassMethod(): void
    {
        $route = new Route('/', [TestController::class, 'textView']);
        $route->match('/');
        $view = new View($route->view(), $route->args(), $this->registry());

        $this->assertEquals('text', $view->execute());
        $this->assertInstanceOf(TestAttribute::class, $view->attributes()[0]);
    }

    public function testControllerObjectMethod(): void
    {
        $controller = new TestController();
        $route = new Route('/', [$controller, 'textView']);
        $route->match('/');
        $view = new View($route->view(), $route->args(), $this->registry());

        $this->assertEquals('text', $view->execute());
        $this->assertInstanceOf(TestAttribute::class, $view->attributes()[0]);
    }

    public function testAttributeFilteringCallableView(): void
    {
        $route = new Route('/', #[TestAttribute, TestAttributeExt, TestAttributeDiff] fn () => 'chuck');
        $view = new View($route->view(), $route->args(), $this->registry());

        $this->assertEquals(3, count($view->attributes()));
        $this->assertEquals(2, count($view->attributes(TestAttribute::class)));
        $this->assertEquals(1, count($view->attributes(TestAttributeExt::class)));
        $this->assertEquals(1, count($view->attributes(TestAttributeDiff::class)));
    }

    public function testAttributeFilteringControllerView(): void
    {
        $route = new Route('/', [TestController::class, 'arrayView']);
        $view = new View($route->view(), $route->args(), $this->registry());

        $this->assertEquals(3, count($view->attributes()));
        $this->assertEquals(2, count($view->attributes(TestAttribute::class)));
        $this->assertEquals(1, count($view->attributes(TestAttributeExt::class)));
        $this->assertEquals(1, count($view->attributes(TestAttributeDiff::class)));
    }

    public function testUntypedClosureParameter(): void
    {
        $this->throws(ContainerException::class, 'Autowired entities');

        $route = new Route('/', #[TestAttribute] fn ($param) => 'chuck');
        $route->match('/');
        $view = new View($route->view(), $route->args(), $this->registry());
        $view->execute();
    }

    public function testReflectFunction(): void
    {
        $rf = View::getReflectionFunction(function () {
        });
        $this->assertInstanceOf(ReflectionFunction::class, $rf);

        $rf = View::getReflectionFunction(new class () {
            public function __invoke(): string
            {
                return '';
            }
        });
        $this->assertInstanceOf(ReflectionMethod::class, $rf);

        $rf = View::getReflectionFunction('is_string');
        $this->assertInstanceOf(ReflectionFunction::class, $rf);
    }

    public function testViewResponse(): void
    {
        $route = new Route('/', function () {
            $response = $this->responseFactory()->createResponse()
                ->withHeader('Content-Type', 'text/plain');
            $response->getBody()->write('Chuck PSR Response');

            return $response;
        });
        $route->match('/');
        $view = new View($route->view(), $route->args(), $this->registry());
        $response = $view->respond($route, $this->registry());

        $this->assertEquals('Chuck PSR Response', (string)$response->getBody());
        $this->assertEquals('text/plain', $response->getHeaders()['Content-Type'][0]);
    }

    public function testViewResponseRendererWithArgsAndOptions(): void
    {
        $registry = $this->registry();
        $registry
            ->tag(Renderer::class)
            ->add('test', TestRendererArgsOptions::class)
            ->args(option1: 13, option2: 'Option');
        $route = (new Route('/', fn () => ['name' => 'Chuck']))
            ->render('test', arg1: 'Arg', arg2: 73);
        $route->match('/');
        $view = new View($route->view(), $route->args(), $registry);
        $response = $view->respond($route, $registry);

        $this->assertEquals('{"name":"Chuck","arg1":"Arg","arg2":73,"option1":13,"option2":"Option"}', (string)$response->getBody());
        $this->assertEquals('application/json', $response->getHeaders()['Content-Type'][0]);
    }

    public function testViewResponseRendererWithOptionsClosure(): void
    {
        $registry = $this->registry();
        $registry
            ->tag(Renderer::class)
            ->add('test', TestRendererArgsOptions::class)
            ->args(fn () => ['option1' => 13, 'option2' => 'Option']);

        $route = (new Route('/', fn () => ['name' => 'Chuck']))->render('test');
        $route->match('/');
        $view = new View($route->view(), $route->args(), $registry);
        $response = $view->respond($route, $registry);

        $this->assertEquals('{"name":"Chuck","option1":13,"option2":"Option"}', (string)$response->getBody());
        $this->assertEquals('application/json', $response->getHeaders()['Content-Type'][0]);
    }
}
