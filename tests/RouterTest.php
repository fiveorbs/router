<?php

declare(strict_types=1);

namespace Conia\Route\Tests;

use Conia\Route\Exception\ContainerException;
use Conia\Route\Exception\HttpMethodNotAllowed;
use Conia\Route\Exception\HttpNotFound;
use Conia\Route\Exception\RuntimeException;
use Conia\Route\Group;
use Conia\Route\Renderer\Render;
use Conia\Route\Renderer\Renderer;
use Conia\Route\Response;
use Conia\Route\Route;
use Conia\Route\Router;
use Conia\Route\Tests\Fixtures\TestController;
use Conia\Route\Tests\Fixtures\TestControllerWithRequest;
use Conia\Route\Tests\Fixtures\TestEndpoint;
use Conia\Route\Tests\Fixtures\TestMiddleware1;
use Conia\Route\Tests\Fixtures\TestRenderer;
use Conia\Route\Tests\Setup\C;
use Psr\Http\Message\ResponseInterface as PsrResponse;

class RouterTest extends TestCase
{
    public function testMatching(): void
    {
        $this->throws(HttpNotFound::class);

        $router = new Router();
        $index = new Route('/', fn () => null, 'index');
        $router->addRoute($index);
        $albums = new Route('/albums', fn () => null);
        $router->addRoute($albums);
        $router->addGroup(new Group('/albums', function (Group $group) {
            $ctrl = TestController::class;
            $group->addRoute(Route::get('/{name}', "{$ctrl}::albumName"));
        }));

        $this->assertEquals('index', $router->match($this->request(method: 'GET', url: ''))->name());

        $this->assertEquals($index, $router->match($this->request(method: 'GET', url: '')));
        $this->assertEquals($albums, $router->match($this->request(method: 'GET', url: '/albums')));
        $this->assertEquals($albums, $router->match($this->request(method: 'GET', url: '/albums?q=Symbolic')));
        $this->assertEquals('', $router->match($this->request(method: 'GET', url: '/albums/name'))->name());

        $router->match($this->request(method: 'GET', url: '/does-not-exist'));
    }

    public function testSimpleMatchingUrlEncoded(): void
    {
        $router = new Router();
        $route = new Route('/album name/...slug', fn () => null, 'encoded');
        $router->addRoute($route);

        $this->assertEquals('encoded', $router->match($this->request(method: 'GET', url: '/album%20name/scream%20bloody%20gore'))->name());
        $this->assertEquals('scream bloody gore', $route->args()['slug']);
    }

    public function testMatchingWithHelpers(): void
    {
        $this->throws(HttpMethodNotAllowed::class);

        $router = new Router();
        $index = $router->get('/', fn () => null, 'index');
        $albums = $router->post('/albums', fn () => null);

        $this->assertEquals('index', $router->match($this->request(method: 'GET', url: ''))->name());
        $this->assertEquals('', $router->match($this->request(method: 'POST', url: '/albums'))->name());
        $this->assertEquals($index, $router->match($this->request(method: 'GET', url: '')));
        $this->assertEquals($albums, $router->match($this->request(method: 'POST', url: '/albums')));

        $router->match($this->request(method: 'GET', url: '/albums'));
    }

    public function testGenerateRouteUrl(): void
    {
        $router = new Router();
        $albums = new Route('albums/{from}/{to}', fn () => null, 'albums');
        $router->addRoute($albums);

        $this->assertEquals('/albums/1990/1995', $router->routeUrl('albums', from: 1990, to: 1995));
        $this->enableHttps();
        $this->assertEquals('/albums/1988/1991', $router->routeUrl('albums', ['from' => 1988, 'to' => 1991]));
        $this->disableHttps();
    }

    public function testFailToGenerateRouteUrl(): void
    {
        $this->throws(RuntimeException::class, 'Route not found');

        $router = new Router();
        $router->routeUrl('fantasy');
    }

    public function testStaticRoutesUnnamed(): void
    {
        $router = new Router();
        $router->addStatic('/static', C::root() . '/public/static');

        $this->assertEquals('/static/test.json', $router->staticUrl('/static', 'test.json'));
        $this->assertMatchesRegularExpression('/\?v=[a-f0-9]{8}$/', $router->staticUrl('/static', 'test.json', true));
        $this->assertMatchesRegularExpression('/\?exists=true&v=[a-f0-9]{8}$/', $router->staticUrl('/static', 'test.json?exists=true', true));
        $this->assertMatchesRegularExpression(
            '/https:\/\/chuck.local\/static\/test.json\?v=[a-f0-9]{8}$/',
            $router->staticUrl(
                '/static',
                'test.json',
                host: 'https://chuck.local/',
                bust: true,
            )
        );
        // Nonexistent files should not have a cachebuster attached
        $this->assertMatchesRegularExpression('/https:\/\/chuck.local\/static\/does-not-exist.json$/', $router->staticUrl(
            '/static',
            'does-not-exist.json',
            host: 'https://chuck.local/',
            bust: true,
        ));
    }

    public function testStaticRoutesNamed(): void
    {
        $router = new Router();
        $router->addStatic('/static', C::root() . '/public/static', 'staticroute');

        $this->assertEquals('/static/test.json', $router->staticUrl('staticroute', 'test.json'));
    }

    public function testStaticRoutesToNonexistentDirectory(): void
    {
        $this->throws(RuntimeException::class, 'does not exist');

        (new Router())->addStatic('/static', C::root() . '/fantasy/dir');
    }

    public function testStaticRouteDuplicateNamed(): void
    {
        $this->throws(RuntimeException::class, 'Duplicate static route: static');

        $router = new Router();
        $router->addStatic('/static', C::root() . '/public/static', 'static');
        $router->addStatic('/anotherstatic', C::root() . '/public/static', 'static');
    }

    public function testStaticRouteDuplicateUnnamed(): void
    {
        $this->throws(RuntimeException::class, 'Duplicate static route: /static');

        $router = new Router();
        $router->addStatic('/static', C::root() . '/public/static');
        $router->addStatic('/static', C::root() . '/public/static');
    }

    public function testDispatchClosure(): void
    {
        $self = $this;
        $router = new Router();
        $index = new Route(
            '/',
            function () use ($self) {
                return Response::fromFactory($self->factory())->html('Chuck', 200);
            }
        );
        $router->addRoute($index);

        $response = $router->dispatch($this->request(method: 'GET', url: '/'), $this->registry());
        $this->assertInstanceOf(PsrResponse::class, $response);
        $this->assertEquals('Chuck', (string)$response->getBody());
    }

    public function testDispatchClassMethodReturingAnArrayWithRenderer(): void
    {
        $router = new Router();
        $route = Route::get('/text', [TestController::class, 'arrayView'])->render('json');
        $router->addRoute($route);
        $response = $router->dispatch($this->request(method: 'GET', url: '/text'), $this->registry());

        $this->assertInstanceOf(PsrResponse::class, $response);
        $this->assertEquals('{"success":true}', (string)$response->getBody());
    }

    public function testDispatchInvokableClass(): void
    {
        $router = new Router();
        $object = new Route('/object', 'Conia\Route\Tests\Fixtures\TestInvokableClass');
        $router->addRoute($object);

        $response = $router->dispatch($this->request(method: 'GET', url: '/object'), $this->registry());
        $this->assertInstanceOf(PsrResponse::class, $response);
        $this->assertEquals('Schuldiner', (string)$response->getBody());
    }

    public function testDispatchControllerWithRequestConstructor(): void
    {
        $router = new Router();
        $index = new Route('/', TestControllerWithRequest::class . '::requestOnly');
        $router->addRoute($index);

        $response = $router->dispatch($this->request(method: 'GET', url: '/'), $this->registry());
        $this->assertEquals('Conia\Route\Request', (string)$response->getBody());
    }

    public function testDispatchClosureWithRenderAttribute(): void
    {
        $registry = $this->registry();
        $registry->tag(Renderer::class)->add('test', TestRenderer::class);

        $router = new Router();
        $index = new Route(
            '/',
            #[Render('test', contentType: 'application/xhtml+xml')]
            function () {
                return 'render attribute';
            }
        );
        $router->addRoute($index);

        $response = $router->dispatch($this->request(method: 'GET', url: '/'), $registry);
        $this->assertEquals('render attribute', $this->fullTrim((string)$response->getBody()));
    }

    public function testDispatchNonexistentControllerView(): void
    {
        $this->throws(RuntimeException::class, 'View method not found');

        $router = new Router();
        $index = new Route('/', TestController::class . '::nonexistentView');
        $router->addRoute($index);

        $router->dispatch($this->request(method: 'GET', url: '/'), $this->registry());
    }

    public function testDispatchNonexistentController(): void
    {
        $this->throws(RuntimeException::class, 'Controller not found');

        $router = new Router();
        $index = new Route('/', NonexisitentTestController::class . '::view');
        $router->addRoute($index);

        $router->dispatch($this->request(method: 'GET', url: '/'), $this->registry());
    }

    public function testDispatchWrongViewReturnType(): void
    {
        $this->throws(RuntimeException::class, 'Unable to determine a response handler');

        $router = new Router();
        $index = new Route('/', TestControllerWithRequest::class . '::wrongReturnType');
        $router->addRoute($index);
        $router->dispatch($this->request(method: 'GET', url: '/'), $this->registry());
    }

    public function testDispatchMissingRoute(): void
    {
        $this->throws(HttpNotFound::class);

        $router = new Router();
        $index = new Route('/', TestControllerWithRequest::class . '::wrongReturnType');
        $router->addRoute($index);
        $router->dispatch($this->request(method: 'GET', url: '/wrong'), $this->registry());
    }

    public function testDispatchViewWithRouteParams(): void
    {
        $router = new Router();
        $index = (new Route('/{string}/{float}-{int}', TestControllerWithRequest::class . '::routeParams'))->render('json');
        $router->addRoute($index);

        $response = $router->dispatch($this->request(method: 'GET', url: '/symbolic/7.13-23'), $this->registry());
        $this->assertInstanceOf(Route::class, $router->getRoute());
        $this->assertEquals('{"string":"symbolic","float":7.13,"int":23,"request":"Conia\\\\Route\\\\Request"}', (string)$response->getBody());
    }

    public function testDispatchViewWithDefaultValueParams(): void
    {
        $index = (new Route('/{string}', TestController::class . '::routeDefaultValueParams'))->render('json');
        $withInt = (new Route(
            '/{string}/{int}',
            TestController::class . '::routeDefaultValueParams'
        ))->render('json');

        $router = new Router();
        $router->addRoute($index);
        $router->addRoute($withInt);
        $response = $router->dispatch($this->request(method: 'GET', url: '/symbolic/17'), $this->registry());

        $this->assertInstanceOf(Route::class, $router->getRoute());
        $this->assertEquals('{"string":"symbolic","int":17}', (string)$response->getBody());

        $router = new Router();
        $router->addRoute($index);
        $router->addRoute($withInt);
        $response = $router->dispatch($this->request(method: 'GET', url: '/symbolic'), $this->registry());

        $this->assertInstanceOf(Route::class, $router->getRoute());
        $this->assertEquals('{"string":"symbolic","int":13}', (string)$response->getBody());
    }

    public function testDispatchViewWithWrongRouteParams(): void
    {
        $this->throws(RuntimeException::class, 'cannot be resolved');

        $router = new Router();
        $index = (new Route('/{wrong}/{param}', TestControllerWithRequest::class . '::routeParams'))->render('json');
        $router->addRoute($index);

        $router->dispatch($this->request(method: 'GET', url: '/symbolic/7.13-23'), $this->registry());
    }

    public function testDispatchViewWithWrongTypeForIntParam(): void
    {
        $this->throws(RuntimeException::class, "Cannot cast 'int' to int");

        $router = new Router();
        $index = (new Route('/{string}/{float}-{int}', TestControllerWithRequest::class . '::routeParams'))->render('json');
        $router->addRoute($index);

        $router->dispatch($this->request(method: 'GET', url: '/symbolic/7.13-wrong'), $this->registry());
    }

    public function testDispatchViewWithWrongTypeForFloatParam(): void
    {
        $this->throws(RuntimeException::class, "Cannot cast 'float' to float");

        $router = new Router();
        $index = (new Route('/{string}/{float}-{int}', TestControllerWithRequest::class . '::routeParams'))->render('json');
        $router->addRoute($index);

        $router->dispatch($this->request(method: 'GET', url: '/symbolic/wrong-13'), $this->registry());
    }

    public function testDispatchViewWithUnsupportedParam(): void
    {
        $this->throws(ContainerException::class, 'unresolvable: GdImage');

        $router = new Router();
        $index = (new Route('/{name}', fn (GdImage $name) => $name))->render('json');
        $router->addRoute($index);

        $router->dispatch($this->request(method: 'GET', url: '/symbolic'), $this->registry());
    }

    public function testAccessUninitializedRoute(): void
    {
        $this->throws(RuntimeException::class, 'Route is not initialized');

        (new Router())->getRoute();
    }

    public function testDuplicateRouteNamed(): void
    {
        $this->throws(RuntimeException::class, 'Duplicate route: index');

        $router = new Router();
        $router->addRoute(new Route('/', fn () => null, 'index'));
        $router->addRoute(new Route('albums', fn () => null, 'index'));
    }

    public function testDispatchViewWithRouteParamsIncludingRequest(): void
    {
        $router = new Router();
        $index = (new Route(
            '/{int}/{string}-{float}',
            TestController::class . '::routeParams'
        ))->render('json');
        $router->addRoute($index);

        $response = $router->dispatch(
            $this->request(method: 'GET', url: '/17/spiritual-healing-23.31'),
            $this->registry()
        );
        $this->assertEquals('{"string":"spiritual-healing","float":23.31,"int":17,"request":"Conia\\\\Route\\\\Request"}', (string)$response->getBody());
    }

    public function testMiddlewareAdd(): void
    {
        $router = new Router();

        $router->middleware('_testFunctionMiddleware');
        $router->middleware(TestMiddleware1::class);

        $this->assertEquals(2, count($router->getMiddleware()));
    }

    public function testFailAfterAddingInvalidMiddleware(): void
    {
        $this->throws(RuntimeException::class, 'Invalid middleware: this-is-no-middleware');

        $router = new Router();
        $router->middleware('this-is-no-middleware');
        $index = new Route('/', fn () => '');
        $router->addRoute($index);

        $router->dispatch($this->request(), $this->registry());
    }

    public function testGETMatching(): void
    {
        $router = new Router();
        $route = Route::get('/', fn () => null);
        $router->addRoute($route);

        $this->assertEquals($route, $router->match($this->request(method: 'GET', url: '/')));
    }

    public function testHEADMatching(): void
    {
        $router = new Router();
        $route = Route::head('/', fn () => null);
        $router->addRoute($route);

        $this->assertEquals($route, $router->match($this->request(method: 'HEAD', url: '/')));
    }

    public function testPUTMatching(): void
    {
        $router = new Router();
        $route = Route::put('/', fn () => null);
        $router->addRoute($route);

        $this->assertEquals($route, $router->match($this->request(method: 'PUT', url: '/')));
    }

    public function testPOSTMatching(): void
    {
        $router = new Router();
        $route = Route::post('/', fn () => null);
        $router->addRoute($route);

        $this->assertEquals($route, $router->match($this->request(method: 'POST', url: '/')));
    }

    public function testPATCHMatching(): void
    {
        $router = new Router();
        $route = Route::patch('/', fn () => null);
        $router->addRoute($route);

        $this->assertEquals($route, $router->match($this->request(method: 'PATCH', url: '/')));
    }

    public function testDELETEMatching(): void
    {
        $router = new Router();
        $route = Route::delete('/', fn () => null);
        $router->addRoute($route);

        $this->assertEquals($route, $router->match($this->request(method: 'DELETE', url: '/')));
    }

    public function testOPTIONSMatching(): void
    {
        $router = new Router();
        $route = Route::options('/', fn () => null);
        $router->addRoute($route);

        $this->assertEquals($route, $router->match($this->request(method: 'OPTIONS', url: '/')));
    }

    public function testMatchingWrongMethod(): void
    {
        $this->throws(HttpMethodNotAllowed::class);

        $router = new Router();
        $route = Route::get('/', fn () => null);
        $router->addRoute($route);

        $this->assertEquals($route, $router->match($this->request(method: 'POST', url: '/')));
    }

    public function testMultipleMethodsMatchingI(): void
    {
        $this->throws(HttpMethodNotAllowed::class);

        $router = new Router();
        $route = Route::get('/', fn () => null)->method('post');
        $router->addRoute($route);

        $this->assertEquals($route, $router->match($this->request(method: 'GET', url: '/')));
        $this->assertEquals($route, $router->match($this->request(method: 'POST', url: '/')));
        $router->match($this->request(method: 'PUT', url: '/'));
    }

    public function testMultipleMethodsMatchingII(): void
    {
        $this->throws(HttpMethodNotAllowed::class);

        $router = new Router();
        $route = (new Route('/', fn () => null))->method('gEt', 'Put');
        $router->addRoute($route);

        $this->assertEquals($route, $router->match($this->request(method: 'GET', url: '/')));
        $this->assertEquals($route, $router->match($this->request(method: 'PUT', url: '/')));
        $router->match($this->request(method: 'POST', url: '/'));
    }

    public function testMultipleMethodsMatchingIII(): void
    {
        $this->throws(HttpMethodNotAllowed::class);

        $router = new Router();
        $route = (new Route('/', fn () => null))->method('get')->method('head');
        $router->addRoute($route);

        $this->assertEquals($route, $router->match($this->request(method: 'GET', url: '/')));
        $this->assertEquals($route, $router->match($this->request(method: 'HEAD', url: '/')));
        $router->match($this->request(method: 'POST', url: '/'));
    }

    public function testAllMethodsMatching(): void
    {
        $router = new Router();
        $route = new Route('/', fn () => null);
        $router->addRoute($route);

        $this->assertEquals($route, $router->match($this->request(method: 'GET', url: '/')));
        $this->assertEquals($route, $router->match($this->request(method: 'HEAD', url: '/')));
        $this->assertEquals($route, $router->match($this->request(method: 'POST', url: '/')));
        $this->assertEquals($route, $router->match($this->request(method: 'PUT', url: '/')));
        $this->assertEquals($route, $router->match($this->request(method: 'PATCH', url: '/')));
        $this->assertEquals($route, $router->match($this->request(method: 'DELETE', url: '/')));
        $this->assertEquals($route, $router->match($this->request(method: 'OPTIONS', url: '/')));
    }

    public function testSamePatternMultipleMethods(): void
    {
        $this->throws(HttpMethodNotAllowed::class);

        $router = new Router();
        $puthead = (new Route('/', fn () => null, 'puthead'))->method('HEAD', 'Put');
        $router->addRoute($puthead);
        $get = (new Route('/', fn () => null, 'get'))->method('GET');
        $router->addRoute($get);

        $this->assertEquals($get, $router->match($this->request(method: 'GET', url: '/')));
        $this->assertEquals($puthead, $router->match($this->request(method: 'PUT', url: '/')));
        $this->assertEquals($puthead, $router->match($this->request(method: 'HEAD', url: '/')));
        $router->match($this->request(method: 'POST', url: '/'));
    }

    public function testAddEndpoint(): void
    {
        $router = new Router();
        $router->endpoint('/endpoints', TestEndpoint::class, ['id', 'category'])->add();

        $route = $router->match($this->request(method: 'POST', url: '/endpoints'));
        $this->assertEquals('/endpoints', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'post'], $route->view());
        $this->assertEquals([], $route->args());
    }

    public function testAddRoutesWithCallback(): void
    {
        $router = new Router();
        $router->routes(function (Router $r): void {
            $r->get('/', fn () => null, 'index');
            $r->post('/albums', fn () => null);
        });

        $this->assertEquals('index', $router->match($this->request(method: 'GET', url: ''))->name());
        $this->assertEquals('', $router->match($this->request(method: 'POST', url: '/albums'))->name());
    }
}
