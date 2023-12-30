<?php

declare(strict_types=1);

namespace Conia\Route\Tests;

use Conia\Route\Exception\MethodNotAllowedException;
use Conia\Route\Exception\RuntimeException;
use Conia\Route\Exception\ValueError;
use Conia\Route\Group;
use Conia\Route\Route;
use Conia\Route\Router;
use Conia\Route\Tests\Fixtures\TestController;
use Conia\Route\Tests\Fixtures\TestEndpoint;
use Conia\Route\Tests\Fixtures\TestMiddleware1;
use Conia\Route\Tests\Fixtures\TestMiddleware2;
use Conia\Route\Tests\Fixtures\TestMiddleware3;

class GroupTest extends TestCase
{
    public function testMatchingNamed(): void
    {
        $router = new Router();
        $index = new Route('/', fn () => null, 'index');
        $router->addRoute($index);

        $group = new Group('/albums', function (Group $group) {
            $ctrl = TestController::class;

            $group->addRoute(Route::get('/home', "{$ctrl}::albumHome", 'home'));
            $group->addRoute(Route::get('/{name}', "{$ctrl}::albumName", 'name'));
            $group->addRoute(Route::get('', "{$ctrl}::albumList", 'list'));
        }, 'albums:');
        $group->create($router);

        $this->assertEquals('index', $router->match($this->request(method: 'GET', uri: ''))->name());
        $this->assertEquals('albums:name', $router->match($this->request(method: 'GET', uri: '/albums/symbolic'))->name());
        $this->assertEquals('albums:home', $router->match($this->request(method: 'GET', uri: '/albums/home'))->name());
        $this->assertEquals('albums:list', $router->match($this->request(method: 'GET', uri: '/albums'))->name());
        $this->assertEquals('/albums/symbolic', $router->routeUrl('albums:name', name: 'symbolic'));
    }

    public function testMatchingUnnamed(): void
    {
        $router = new Router();
        $index = new Route('/', fn () => null);
        $router->addRoute($index);

        $group = new Group('/albums', function (Group $group) {
            $ctrl = TestController::class;

            $group->addRoute(Route::get('/home', "{$ctrl}::albumHome"));
            $group->addRoute(Route::get('/{name}', "{$ctrl}::albumName"));
            $group->addRoute(Route::get('', "{$ctrl}::albumList"));
        });
        $group->create($router);

        $this->assertEquals('', $router->match($this->request('GET', ''))->name());
        $this->assertEquals('', $router->match($this->request('GET', '/albums/symbolic'))->name());
        $this->assertEquals('', $router->match($this->request('GET', '/albums/home'))->name());
        $this->assertEquals('', $router->match($this->request('GET', '/albums'))->name());
    }

    public function testMatchingWithHelperMethods(): void
    {
        $this->throws(MethodNotAllowedException::class);

        $router = new Router();
        $index = new Route('/', fn () => null);
        $router->addRoute($index);

        $group = new Group('/helper', function (Group $group) {
            $ctrl = TestController::class;

            $group->get('/get', "{$ctrl}::albumHome", 'getroute');
            $group->post('/post', "{$ctrl}::albumHome", 'postroute');
            $group->put('/put', "{$ctrl}::albumHome", 'putroute');
            $group->patch('/patch', "{$ctrl}::albumHome", 'patchroute');
            $group->delete('/delete', "{$ctrl}::albumHome", 'deleteroute');
            $group->options('/options', "{$ctrl}::albumHome", 'optionsroute');
            $group->head('/head', "{$ctrl}::albumHome", 'headroute');
            $group->route('/route', "{$ctrl}::albumHome", 'allroute');
        }, 'helper:');
        $group->create($router);

        $this->assertEquals('helper:getroute', $router->match($this->request('GET', '/helper/get'))->name());
        $this->assertEquals('helper:postroute', $router->match($this->request('POST', '/helper/post'))->name());
        $this->assertEquals('helper:putroute', $router->match($this->request('PUT', '/helper/put'))->name());
        $this->assertEquals('helper:patchroute', $router->match($this->request('PATCH', '/helper/patch'))->name());
        $this->assertEquals('helper:deleteroute', $router->match($this->request('DELETE', '/helper/delete'))->name());
        $this->assertEquals('helper:optionsroute', $router->match($this->request('OPTIONS', '/helper/options'))->name());
        $this->assertEquals('helper:headroute', $router->match($this->request('HEAD', '/helper/head'))->name());
        $this->assertEquals('helper:allroute', $router->match($this->request('GET', '/helper/route'))->name());
        $this->assertEquals('helper:allroute', $router->match($this->request('HEAD', '/helper/route'))->name());

        // raises not allowed
        $router->match($this->request('GET', '/helper/delete'));
    }

    // public function testRenderer(): void
    // {
    //     $router = new Router();
    //
    //     $group = (new Group('/albums', function (Group $group) {
    //         $ctrl = TestController::class;
    //
    //         $group->addRoute(Route::get('', "{$ctrl}::albumList"));
    //
    //         // overwrite group renderer
    //         $group->addRoute(Route::get('/home', "{$ctrl}::albumHome")->render('template:home.php'));
    //
    //         $group->addRoute(Route::get('/{name}', "{$ctrl}::albumName"));
    //     }))->render('json');
    //     $group->create($router);
    //
    //     $route = $router->match($this->request(method: 'GET', uri: '/albums/human'));
    //     $this->assertEquals('json', $route->renderer()->type);
    //
    //     $route = $router->match($this->request(method: 'GET', uri: '/albums/home'));
    //     $this->assertEquals('template:home.php', $route->renderer()->type);
    //
    //     $route = $router->match($this->request(method: 'GET', uri: '/albums'));
    //     $this->assertEquals('json', $route->renderer()->type);
    // }

    public function testControllerPrefixing(): void
    {
        $router = new Router();
        $index = new Route('/', fn () => null);
        $router->addRoute($index);

        $group = (new Group('/albums', function (Group $group) {
            $group->addRoute(Route::get('-list', 'albumList', 'list'));
        }, 'albums-'))->controller(TestController::class);
        $group->create($router);

        $route = $router->match($this->request(method: 'GET', uri: '/albums-list'));
        $this->assertEquals('albums-list', $route->name());
        $this->assertEquals([TestController::class, 'albumList'], $route->view());
    }

    public function testEndpointInGroup(): void
    {
        $router = new Router();
        $index = new Route('/', fn () => null);
        $router->addRoute($index);

        (new Group('/media', function (Group $group) {
            $group->endpoint('/albums', TestEndpoint::class, 'id')->name('albums')->add();
        }, 'media-'))->create($router);

        $route = $router->match($this->request(method: 'GET', uri: '/media/albums/666'));
        $this->assertEquals('media-albums-get', $route->name());
        $this->assertEquals([TestEndpoint::class, 'get'], $route->view());
        $this->assertEquals(['id' => '666'], $route->args());
    }

    public function testNestedGroups(): void
    {
        $router = new Router();
        $mw1 = new TestMiddleware1();
        $mw2 = new TestMiddleware2();
        $mw3 = new TestMiddleware3();

        (new Group('/media', function (Group $group) use ($mw1, $mw2, $mw3) {
            // Create using ::group - will not be created immediately
            $group->group('/music', function (Group $group) use ($mw1, $mw2, $mw3) {
                // Create using ::addGroup - will internally be created immediately
                $group->addGroup((new Group('/albums', function (Group $group) use ($mw1, $mw3) {
                    // Create using ::group shortcut and create immediately
                    $group->group('/songs', function (Group $group) use ($mw1) {
                        // Create  in place - checks if it skips already created groups
                        $group->endpoint('/times', TestEndpoint::class, 'id')
                            ->name('times')
                            ->middleware($mw1)
                            ->add();
                    }, 'songs-')->middleware($mw3)->create($group);
                }, 'albums-'))->middleware($mw2));
            }, 'music-');
        }, 'media-'))->middleware($mw1)->create($router);

        $route = $router->match($this->request(method: 'GET', uri: '/media/music/albums/songs/times/666'));
        $this->assertEquals('media-music-albums-songs-times-get', $route->name());
        $this->assertEquals([TestEndpoint::class, 'get'], $route->view());
        $this->assertEquals('/media/music/albums/songs/times/{id}', $route->pattern());
        $this->assertEquals(['id' => '666'], $route->args());
        $this->assertEquals([$mw1, $mw2, $mw3, $mw1], $route->getMiddleware());
    }

    public function testControllerPrefixingErrorUsingClosure(): void
    {
        $this->throws(ValueError::class, 'Cannot add controller');

        $router = new Router();

        $group = (new Group('/albums', function (Group $group) {
            $group->addRoute(
                Route::get('-list', function () {
                })
            );
        }))->controller(TestController::class);
        $group->create($router);
    }

    public function testControllerPrefixingErrorUsingEndpoint(): void
    {
        $this->throws(ValueError::class, 'Cannot add controller');

        $router = new Router();

        $group = (new Group('/media', function (Group $group) {
            $group->endpoint('/albums', TestEndpoint::class, 'id')->name('albums')->add();
        }))->controller(TestController::class);
        $group->create($router);
    }

    public function testMiddleware(): void
    {
        $router = new Router();

        $group = (new Group('/albums', function (Group $group) {
            $ctrl = TestController::class;

            $group->addRoute(Route::get('', "{$ctrl}::albumList"));
            $group->addRoute(Route::get('/home', "{$ctrl}::albumHome")->middleware(new TestMiddleware3()));
            $group->addRoute(Route::get('/{name}', "{$ctrl}::albumName"));
        }))->middleware(new TestMiddleware2());
        $group->create($router);

        $route = $router->match($this->request(method: 'GET', uri: '/albums/human'));
        $middleware = $route->getMiddleware();
        $this->assertEquals(1, count($middleware));
        $this->assertInstanceof(TestMiddleware2::class, $middleware[0]);

        $route = $router->match($this->request(method: 'GET', uri: '/albums/home'));
        $middleware = $route->getMiddleware();
        $this->assertEquals(2, count($middleware));
        $this->assertInstanceof(TestMiddleware2::class, $middleware[0]);
        $this->assertInstanceof(TestMiddleware3::class, $middleware[1]);
    }

    public function testFailWithoutCallingCreateBefore(): void
    {
        $this->throws(RuntimeException::class, 'RouteAdder not set');

        $group = new Group('/albums', function (Group $group) {
        }, 'test:');
        $group->addRoute(Route::get('/', fn () => ''));
    }
}
