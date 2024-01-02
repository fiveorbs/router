<?php

declare(strict_types=1);

namespace Conia\Route\Tests;

use Conia\Route\Group;
use Conia\Route\Route;
use Conia\Route\Router;
use Conia\Route\Tests\Fixtures\TestAfterAddHeader;
use Conia\Route\Tests\Fixtures\TestAfterAddText;
use Conia\Route\Tests\Fixtures\TestAfterRendererJson;
use Conia\Route\Tests\Fixtures\TestAfterRendererText;
use Conia\Route\Tests\Fixtures\TestBeforeFirst;
use Conia\Route\Tests\Fixtures\TestBeforeSecond;
use Conia\Route\View;
use PHPUnit\Framework\Attributes\Group as G;

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

    #[G('only')]
    public function testRenderer(): void
    {
        $router = new Router();

        $group = (new Group('/albums', function (Group $group) {
            $ctrl = TestController::class;

            $group->addRoute(Route::get('', "{$ctrl}::albumList"));

            // overwrite group renderer
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
}
