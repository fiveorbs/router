<?php

declare(strict_types=1);

namespace Conia\Route\Tests;

use Conia\Registry\Registry;
use Conia\Route\Exception\ContainerException;
use Conia\Route\Factory;
use Conia\Route\Http\View;
use Conia\Route\Renderer\Renderer;
use Conia\Route\Request;
use Conia\Route\Response;
use Conia\Route\Route;
use Conia\Route\Tests\Fixtures\TestAttribute;
use Conia\Route\Tests\Fixtures\TestAttributeDiff;
use Conia\Route\Tests\Fixtures\TestAttributeExt;
use Conia\Route\Tests\Fixtures\TestAttributeViewAttr;
use Conia\Route\Tests\Fixtures\TestController;
use Conia\Route\Tests\Fixtures\TestRendererArgsOptions;
use Conia\Route\Tests\Fixtures\TestResponse;
use Conia\Route\Tests\Setup\TestCase;

require __DIR__ . '/Setup/globalSymbols.php';

class ViewTest extends TestCase
{
    public function testClosure(): void
    {
        $route = new Route('/', #[TestAttribute] fn () => 'chuck');
        $route->match('/');
        $view = new View($route->view(), $route->args(), $this->registry());

        expect($view->execute())->toBe('chuck');
        expect($view->attributes()[0])->toBeInstanceOf(TestAttribute::class);
    }

    public function testFunction(): void
    {
        $route = new Route('/{name}', '_testViewWithAttribute');
        $route->match('/symbolic');
        $view = new View($route->view(), $route->args(), $this->registry());

        expect($view->execute())->toBe('symbolic');
        expect($view->attributes()[0])->toBeInstanceOf(TestAttribute::class);
    }

    public function testControllerString(): void
    {
        $route = new Route('/', '\Conia\Route\Tests\Fixtures\TestController::textView');
        $route->match('/');
        $view = new View($route->view(), $route->args(), $this->registry());

        expect($view->execute())->toBe('text');
        expect($view->attributes()[0])->toBeInstanceOf(TestAttribute::class);
    }

    public function testControllerClassMethod(): void
    {
        $route = new Route('/', [TestController::class, 'textView']);
        $route->match('/');
        $view = new View($route->view(), $route->args(), $this->registry());

        expect($view->execute())->toBe('text');
        expect($view->attributes()[0])->toBeInstanceOf(TestAttribute::class);
    }

    public function testControllerObjectMethod(): void
    {
        $controller = new TestController();
        $route = new Route('/', [$controller, 'textView']);
        $route->match('/');
        $view = new View($route->view(), $route->args(), $this->registry());

        expect($view->execute())->toBe('text');
        expect($view->attributes()[0])->toBeInstanceOf(TestAttribute::class);
    }

    public function testAttributeFilteringCallableView(): void
    {
        $route = new Route('/', #[TestAttribute, TestAttributeExt, TestAttributeDiff] fn () => 'chuck');
        $view = new View($route->view(), $route->args(), $this->registry());

        expect(count($view->attributes()))->toBe(3);
        expect(count($view->attributes(TestAttribute::class)))->toBe(2);
        expect(count($view->attributes(TestAttributeExt::class)))->toBe(1);
        expect(count($view->attributes(TestAttributeDiff::class)))->toBe(1);
    }

    public function testAttributeFilteringControllerView(): void
    {
        $route = new Route('/', [TestController::class, 'arrayView']);
        $view = new View($route->view(), $route->args(), $this->registry());

        expect(count($view->attributes()))->toBe(3);
        expect(count($view->attributes(TestAttribute::class)))->toBe(2);
        expect(count($view->attributes(TestAttributeExt::class)))->toBe(1);
        expect(count($view->attributes(TestAttributeDiff::class)))->toBe(1);
    }

    public function testAttributeWithCallAttribute(): void
    {
        $route = new Route('/', #[TestAttributeViewAttr] fn () => '');
        $route->match('/');
        $view = new View($route->view(), $route->args(), $this->registry());

        $attr = $view->attributes()[0];

        expect($attr->registry)->toBeInstanceOf(Registry::class);
        expect($attr->request)->toBeInstanceOf(Request::class);
        expect($attr->after)->toBe('Called after');
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
        expect($rf)->toBeInstanceOf(ReflectionFunction::class);

        $rf = View::getReflectionFunction(new class () {
            public function __invoke(): string
            {
                return '';
            }
        });
        expect($rf)->toBeInstanceOf(ReflectionMethod::class);

        $rf = View::getReflectionFunction('is_string');
        expect($rf)->toBeInstanceOf(ReflectionFunction::class);
    }

    public function testViewResponseResponse(): void
    {
        $route = new Route('/', function (Registry $registry): Response {
            $factory = $registry->get(Factory::class);
            $response = new Response($factory->response(), $factory);
            $response->write('Chuck Response');
            $response->header('Content-Type', 'text/plain');

            return $response;
        });
        $route->match('/');
        $view = new View($route->view(), $route->args(), $this->registry());
        $response = $view->respond($route, $this->registry());

        expect((string)$response->getBody())->toBe('Chuck Response');
        expect($response->getHeaders()['Content-Type'][0])->toBe('text/plain');
    }

    public function testViewResponsePSRResponse(): void
    {
        $route = new Route('/', function (Registry $registry) {
            $factory = $registry->get(Factory::class);

            return $factory->response()
                ->withBody($factory->stream('Chuck PSR Response'))
                ->withHeader('Content-Type', 'text/plain');
        });
        $route->match('/');
        $view = new View($route->view(), $route->args(), $this->registry());
        $response = $view->respond($route, $this->registry());

        expect((string)$response->getBody())->toBe('Chuck PSR Response');
        expect($response->getHeaders()['Content-Type'][0])->toBe('text/plain');
    }

    public function testViewResponseResponseWrapper(): void
    {
        $route = new Route('/', function (Registry $registry) {
            $factory = $registry->get(Factory::class);

            return new TestResponse($factory->response()
                ->withBody($factory->stream('Chuck ResponseWrapper'))
                ->withHeader('Content-Type', 'text/plain'));
        });
        $route->match('/');
        $view = new View($route->view(), $route->args(), $this->registry());
        $response = $view->respond($route, $this->registry());

        expect((string)$response->getBody())->toBe('Chuck ResponseWrapper');
        expect($response->getHeaders()['Content-Type'][0])->toBe('text/plain');
    }

    public function testViewResponseRenderer(): void
    {
        $route = (new Route('/', fn () => ['name' => 'Chuck']))->render('json');
        $route->match('/');
        $view = new View($route->view(), $route->args(), $this->registry());
        $response = $view->respond($route, $this->registry());

        expect((string)$response->getBody())->toBe('{"name":"Chuck"}');
        expect($response->getHeaders()['Content-Type'][0])->toBe('application/json');
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

        expect((string)$response->getBody())
            ->toBe('{"name":"Chuck","arg1":"Arg","arg2":73,"option1":13,"option2":"Option"}');
        expect($response->getHeaders()['Content-Type'][0])->toBe('application/json');
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

        expect((string)$response->getBody())->toBe('{"name":"Chuck","option1":13,"option2":"Option"}');
        expect($response->getHeaders()['Content-Type'][0])->toBe('application/json');
    }
}
