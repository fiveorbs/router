<?php

declare(strict_types=1);

namespace Conia\Route\Tests;

use Conia\Route\Dispatcher;
use Conia\Route\Group;
use Conia\Route\Route;
use Conia\Route\Router;
use Conia\Route\Tests\Fixtures\TestAfterAddHeader;
use Conia\Route\Tests\Fixtures\TestAfterAddText;
use Conia\Route\Tests\Fixtures\TestAfterRendererJson;
use Conia\Route\Tests\Fixtures\TestAfterRendererText;
use Conia\Route\Tests\Fixtures\TestBeforeFirst;
use Conia\Route\Tests\Fixtures\TestBeforeReplace;
use Conia\Route\Tests\Fixtures\TestBeforeSecond;
use Conia\Route\Tests\Fixtures\TestBeforeThird;
use Conia\Route\View;
use PHPUnit\Framework\Attributes\Group as G;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class BeforeAfterTest extends TestCase
{
    public function testRouteBeforeHandlers(): void
    {
        $route = Route::get('/', fn () => 'chuck');
        $route->before(new TestBeforeFirst())->before(new TestBeforeSecond());
        $handlers = $route->beforeHandlers();

        $this->assertInstanceof(TestBeforeFirst::class, $handlers[0]);
        $this->assertInstanceof(TestBeforeSecond::class, $handlers[1]);
    }

    public function testRouteAfterHandlers(): void
    {
        $route = Route::get('/', fn () => 'chuck');
        $route->after(new TestAfterRendererText($this->responseFactory()))->after(new TestAfterAddText());
        $handlers = $route->afterHandlers();

        $this->assertInstanceof(TestAfterRendererText::class, $handlers[0]);
        $this->assertInstanceof(TestAfterAddText::class, $handlers[1]);
    }

    public function testRouteAfterHandlersReplace(): void
    {
        $factory = $this->responseFactory();
        $route = Route::get('/', fn () => 'chuck');
        $route->after(new TestAfterRendererText($factory))
            ->after(new TestAfterAddText())
            ->after(new TestAfterRendererJson($factory));
        $handlers = $route->afterHandlers();

        $this->assertInstanceof(TestAfterRendererJson::class, $handlers[0]);
        $this->assertInstanceof(TestAfterAddText::class, $handlers[1]);
    }

    public function testGroupAfterHandler(): void
    {
        $router = new Router();

        $group = (new Group('/albums', function (Group $group) {
            $ctrl = TestController::class;

            $group->addRoute(Route::get('', "{$ctrl}::albumList"));

            // overwrite first after renderer
            $group->addRoute(Route::get('/home', "{$ctrl}::albumHome")
                ->after(new TestAfterRendererText($this->responseFactory())));

            $group->addRoute(Route::get('/{name}', "{$ctrl}::albumName"))->after(new TestAfterAddText());
        }))->after(new TestAfterRendererJson($this->responseFactory()))->after(new TestAfterAddHeader());
        $group->create($router);

        $route = $router->match($this->request(method: 'GET', uri: '/albums/human'));
        $this->assertInstanceof(TestAfterRendererJson::class, $route->afterHandlers()[0]);
        $this->assertInstanceof(TestAfterAddHeader::class, $route->afterHandlers()[1]);
        $this->assertInstanceof(TestAfterAddText::class, $route->afterHandlers()[2]);

        $route = $router->match($this->request(method: 'GET', uri: '/albums/home'));
        $this->assertInstanceof(TestAfterRendererText::class, $route->afterHandlers()[0]);
        $this->assertInstanceof(TestAfterAddHeader::class, $route->afterHandlers()[1]);
        $this->assertFalse(isset($route->afterHandlers()[2]));

        $route = $router->match($this->request(method: 'GET', uri: '/albums'));
        $this->assertInstanceof(TestAfterRendererJson::class, $route->afterHandlers()[0]);
        $this->assertInstanceof(TestAfterAddHeader::class, $route->afterHandlers()[1]);
        $this->assertFalse(isset($route->afterHandlers()[2]));
    }

    public function testGroupBeforeHandler(): void
    {
        $router = new Router();

        $group = (new Group('/albums', function (Group $group) {
            $ctrl = TestController::class;

            $group->addRoute(Route::get('', "{$ctrl}::albumList"));

            // overwrite first before
            $group->addRoute(Route::get('/home', "{$ctrl}::albumHome")
                ->before(new TestBeforeReplace()));

            $group->addRoute(Route::get('/{name}', "{$ctrl}::albumName"))->before(new TestBeforeThird());
        }))->before(new TestBeforeFirst())->before(new TestBeforeSecond());
        $group->create($router);

        $route = $router->match($this->request(method: 'GET', uri: '/albums/human'));
        $this->assertInstanceof(TestBeforeFirst::class, $route->beforeHandlers()[0]);
        $this->assertInstanceof(TestBeforeSecond::class, $route->beforeHandlers()[1]);
        $this->assertInstanceof(TestBeforeThird::class, $route->beforeHandlers()[2]);

        $route = $router->match($this->request(method: 'GET', uri: '/albums/home'));
        $this->assertInstanceof(TestBeforeReplace::class, $route->beforeHandlers()[0]);
        $this->assertInstanceof(TestBeforeSecond::class, $route->beforeHandlers()[1]);
        $this->assertFalse(isset($route->beforeHandlers()[2]));

        $route = $router->match($this->request(method: 'GET', uri: '/albums'));
        $this->assertInstanceof(TestBeforeFirst::class, $route->beforeHandlers()[0]);
        $this->assertInstanceof(TestBeforeSecond::class, $route->beforeHandlers()[1]);
        $this->assertFalse(isset($route->beforeHandlers()[2]));
    }

    public function testViewBeforeHandler(): void
    {
        $route = Route::any('/', fn () => 'conia')
            ->before(new TestBeforeReplace())
            ->before(new TestBeforeThird());
        $view = new View(
            $route,
            null,
            [new TestBeforeFirst(), new TestBeforeSecond()],
            [],
        );

        $this->assertInstanceOf(TestBeforeReplace::class, $view->beforeHandlers()[0]);
        $this->assertInstanceOf(TestBeforeSecond::class, $view->beforeHandlers()[1]);
        $this->assertInstanceOf(TestBeforeThird::class, $view->beforeHandlers()[2]);
    }

    public function testViewAfterHandler(): void
    {
        $factory = $this->responseFactory();
        $route = Route::any('/', fn () => 'conia')
            ->after(new TestAfterRendererText($factory))
            ->after(new TestAfterAddText());
        $view = new View(
            $route,
            null,
            [],
            [new TestAfterRendererJson($factory), new TestAfterAddHeader()]
        );

        $this->assertInstanceOf(TestAfterRendererText::class, $view->afterHandlers()[0]);
        $this->assertInstanceOf(TestAfterAddHeader::class, $view->afterHandlers()[1]);
        $this->assertInstanceOf(TestAfterAddText::class, $view->afterHandlers()[2]);
    }

    public function testDispatcherBeforeHandler(): void
    {
        $route = new Route(
            '/',
            function (Request $request) {
                $response = $this->responseFactory()->createResponse()->withHeader('Content-Type', 'text/html');
                $response->getBody()->write(
                    $request->getAttribute('first') . ' ' .
                    $request->getAttribute('second') . ' ' .
                    $request->getAttribute('third')
                );

                return $response;
            }
        );
        $route->before(new TestBeforeReplace())->before(new TestBeforeThird());
        $dispatcher = new Dispatcher();
        $dispatcher->before(new TestBeforeFirst());
        $dispatcher->before(new TestBeforeSecond());
        $response = $dispatcher->dispatch($this->request('GET', '/'), $route);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('replaced-added-by-second second-value third-value', (string)$response->getBody());
    }

    public function testDispatcherAfterHandler(): void
    {
        $factory = $this->responseFactory();
        $route = new Route(
            '/',
            function () {
                return 'Conia';
            }
        );
        $route->after(new TestAfterRendererText($factory))->after(new TestAfterAddHeader());
        $dispatcher = new Dispatcher();
        $dispatcher->after(new TestAfterRendererJson($factory));
        $dispatcher->after(new TestAfterAddText());
        $response = $dispatcher->dispatch($this->request('GET', '/'), $route);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('Conia-appended', (string)$response->getBody());
        $this->assertSame('header-value', (string)$response->getHeaderLine('added-header'));
        $this->assertSame('text/plain', (string)$response->getHeaderLine('Content-Type'));
    }
}
