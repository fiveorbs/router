<?php

declare(strict_types=1);

namespace Conia\Route\Tests;

use Conia\Route\Endpoint;
use Conia\Route\Router;
use Conia\Route\Tests\Fixtures\TestEndpoint;

class EmitterTest extends TestCase
{
    public function testEndpointWithDefaults(): void
    {
        $router = new Router();
        (new Endpoint($router, '/endpoints', TestEndpoint::class, 'id'))->add();

        $route = $router->match($this->request(method: 'DELETE', url: '/endpoints'));
        $this->assertEquals('/endpoints', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'deleteList'], $route->view());
        $this->assertEquals([], $route->args());

        $route = $router->match($this->request(method: 'DELETE', url: '/endpoints/13'));
        $this->assertEquals('/endpoints/{id}', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'delete'], $route->view());
        $this->assertEquals(['id' => '13'], $route->args());

        $route = $router->match($this->request(method: 'GET', url: '/endpoints'));
        $this->assertEquals('/endpoints', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'list'], $route->view());
        $this->assertEquals([], $route->args());

        $route = $router->match($this->request(method: 'GET', url: '/endpoints/13'));
        $this->assertEquals('/endpoints/{id}', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'get'], $route->view());
        $this->assertEquals(['id' => '13'], $route->args());

        $route = $router->match($this->request(method: 'HEAD', url: '/endpoints'));
        $this->assertEquals('/endpoints', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'headList'], $route->view());
        $this->assertEquals([], $route->args());

        $route = $router->match($this->request(method: 'HEAD', url: '/endpoints/13'));
        $this->assertEquals('/endpoints/{id}', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'head'], $route->view());
        $this->assertEquals(['id' => '13'], $route->args());

        $route = $router->match($this->request(method: 'OPTIONS', url: '/endpoints'));
        $this->assertEquals('/endpoints', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'optionsList'], $route->view());
        $this->assertEquals([], $route->args());

        $route = $router->match($this->request(method: 'OPTIONS', url: '/endpoints/13'));
        $this->assertEquals('/endpoints/{id}', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'options'], $route->view());
        $this->assertEquals(['id' => '13'], $route->args());

        $route = $router->match($this->request(method: 'PATCH', url: '/endpoints/13'));
        $this->assertEquals('/endpoints/{id}', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'patch'], $route->view());
        $this->assertEquals(['id' => '13'], $route->args());

        $route = $router->match($this->request(method: 'POST', url: '/endpoints'));
        $this->assertEquals('/endpoints', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'post'], $route->view());
        $this->assertEquals([], $route->args());

        $route = $router->match($this->request(method: 'PUT', url: '/endpoints/13'));
        $this->assertEquals('/endpoints/{id}', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'put'], $route->view());
        $this->assertEquals(['id' => '13'], $route->args());
    }

    public function testEndpointWithPluralSingular(): void
    {
        $router = new Router();
        (new Endpoint($router, ['/endpoints', '/endpoint'], TestEndpoint::class, 'id'))->add();

        $route = $router->match($this->request(method: 'DELETE', url: '/endpoints'));
        $this->assertEquals('/endpoints', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'deleteList'], $route->view());
        $this->assertEquals([], $route->args());

        $route = $router->match($this->request(method: 'DELETE', url: '/endpoint/13'));
        $this->assertEquals('/endpoint/{id}', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'delete'], $route->view());
        $this->assertEquals(['id' => '13'], $route->args());

        $route = $router->match($this->request(method: 'GET', url: '/endpoints'));
        $this->assertEquals('/endpoints', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'list'], $route->view());
        $this->assertEquals([], $route->args());

        $route = $router->match($this->request(method: 'GET', url: '/endpoint/13'));
        $this->assertEquals('/endpoint/{id}', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'get'], $route->view());
        $this->assertEquals(['id' => '13'], $route->args());

        $route = $router->match($this->request(method: 'HEAD', url: '/endpoints'));
        $this->assertEquals('/endpoints', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'headList'], $route->view());
        $this->assertEquals([], $route->args());

        $route = $router->match($this->request(method: 'HEAD', url: '/endpoint/13'));
        $this->assertEquals('/endpoint/{id}', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'head'], $route->view());
        $this->assertEquals(['id' => '13'], $route->args());

        $route = $router->match($this->request(method: 'OPTIONS', url: '/endpoints'));
        $this->assertEquals('/endpoints', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'optionsList'], $route->view());
        $this->assertEquals([], $route->args());

        $route = $router->match($this->request(method: 'OPTIONS', url: '/endpoint/13'));
        $this->assertEquals('/endpoint/{id}', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'options'], $route->view());
        $this->assertEquals(['id' => '13'], $route->args());

        $route = $router->match($this->request(method: 'PATCH', url: '/endpoint/13'));
        $this->assertEquals('/endpoint/{id}', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'patch'], $route->view());
        $this->assertEquals(['id' => '13'], $route->args());

        $route = $router->match($this->request(method: 'POST', url: '/endpoints'));
        $this->assertEquals('/endpoints', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'post'], $route->view());
        $this->assertEquals([], $route->args());

        $route = $router->match($this->request(method: 'PUT', url: '/endpoint/13'));
        $this->assertEquals('/endpoint/{id}', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'put'], $route->view());
        $this->assertEquals(['id' => '13'], $route->args());
    }

    public function testEndpointWithName(): void
    {
        $router = new Router();
        (new Endpoint($router, '/endpoints', TestEndpoint::class, 'id'))->name('albums')->add();

        $route = $router->match($this->request(method: 'DELETE', url: '/endpoints'));
        $this->assertEquals('albums-deleteList', $route->name());

        $route = $router->match($this->request(method: 'DELETE', url: '/endpoints/13'));
        $this->assertEquals('albums-delete', $route->name());

        $route = $router->match($this->request(method: 'GET', url: '/endpoints'));
        $this->assertEquals('albums-list', $route->name());

        $route = $router->match($this->request(method: 'GET', url: '/endpoints/13'));
        $this->assertEquals('albums-get', $route->name());

        $route = $router->match($this->request(method: 'HEAD', url: '/endpoints'));
        $this->assertEquals('albums-headList', $route->name());

        $route = $router->match($this->request(method: 'HEAD', url: '/endpoints/13'));
        $this->assertEquals('albums-head', $route->name());

        $route = $router->match($this->request(method: 'OPTIONS', url: '/endpoints'));
        $this->assertEquals('albums-optionsList', $route->name());

        $route = $router->match($this->request(method: 'OPTIONS', url: '/endpoints/13'));
        $this->assertEquals('albums-options', $route->name());

        $route = $router->match($this->request(method: 'PATCH', url: '/endpoints/13'));
        $this->assertEquals('albums-patch', $route->name());

        $route = $router->match($this->request(method: 'POST', url: '/endpoints'));
        $this->assertEquals('albums-post', $route->name());

        $route = $router->match($this->request(method: 'PUT', url: '/endpoints/13'));
        $this->assertEquals('albums-put', $route->name());
    }

    public function testEndpointWithAttributes(): void
    {
        $router = new Router();
        (new Endpoint($router, '/endpoints', TestEndpoint::class, 'id'))->attrs(cat: 'albums')->add();

        $route = $router->match($this->request(method: 'DELETE', url: '/endpoints'));
        $this->assertEquals(['cat' => 'albums'], $route->getAttrs());

        $route = $router->match($this->request(method: 'DELETE', url: '/endpoints/13'));
        $this->assertEquals(['cat' => 'albums'], $route->getAttrs());

        $route = $router->match($this->request(method: 'GET', url: '/endpoints'));
        $this->assertEquals(['cat' => 'albums'], $route->getAttrs());

        $route = $router->match($this->request(method: 'GET', url: '/endpoints/13'));
        $this->assertEquals(['cat' => 'albums'], $route->getAttrs());

        $route = $router->match($this->request(method: 'HEAD', url: '/endpoints'));
        $this->assertEquals(['cat' => 'albums'], $route->getAttrs());

        $route = $router->match($this->request(method: 'HEAD', url: '/endpoints/13'));
        $this->assertEquals(['cat' => 'albums'], $route->getAttrs());

        $route = $router->match($this->request(method: 'OPTIONS', url: '/endpoints'));
        $this->assertEquals(['cat' => 'albums'], $route->getAttrs());

        $route = $router->match($this->request(method: 'OPTIONS', url: '/endpoints/13'));
        $this->assertEquals(['cat' => 'albums'], $route->getAttrs());

        $route = $router->match($this->request(method: 'PATCH', url: '/endpoints/13'));
        $this->assertEquals(['cat' => 'albums'], $route->getAttrs());

        $route = $router->match($this->request(method: 'POST', url: '/endpoints'));
        $this->assertEquals(['cat' => 'albums'], $route->getAttrs());

        $route = $router->match($this->request(method: 'PUT', url: '/endpoints/13'));
        $this->assertEquals(['cat' => 'albums'], $route->getAttrs());
    }

    public function testEndpointDefaultRenderer(): void
    {
        $router = new Router();
        (new Endpoint($router, '/endpoint', TestEndpoint::class, 'id'))->add();
        $route = $router->match($this->request(method: 'GET', url: '/endpoint'));
        $rendererConfig = $route->getRenderer();

        $this->assertEquals('json', $rendererConfig->type);
        $this->assertEquals([], $rendererConfig->args);
    }

    public function testEndpointSetRenderer(): void
    {
        $router = new Router();
        (new Endpoint($router, '/endpoint', TestEndpoint::class, 'id'))->render('text', 1, 'test')->add();
        $route = $router->match($this->request(method: 'GET', url: '/endpoint'));
        $rendererConfig = $route->getRenderer();

        $this->assertEquals('text', $rendererConfig->type);
        $this->assertEquals([1, 'test'], $rendererConfig->args);
    }

    public function testEndpointWithMultipleArgs(): void
    {
        $router = new Router();
        (new Endpoint($router, '/endpoints', TestEndpoint::class, ['id', 'category']))->add();

        $route = $router->match($this->request(method: 'POST', url: '/endpoints'));
        $this->assertEquals('/endpoints', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'post'], $route->view());
        $this->assertEquals([], $route->args());

        $route = $router->match($this->request(method: 'PUT', url: '/endpoints/13/albums'));
        $this->assertEquals('/endpoints/{id}/{category}', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'put'], $route->view());
        $this->assertEquals(['id' => '13', 'category' => 'albums'], $route->args());
    }
}
