<?php

declare(strict_types=1);

namespace Conia\Route\Tests;

use Conia\Route\Endpoint;
use Conia\Route\Router;
use Conia\Route\Tests\Fixtures\TestEndpoint;

class EndpointTest extends TestCase
{
    public function testEndpointWithDefaults(): void
    {
        $router = new Router();
        (new Endpoint($router, '/endpoints', TestEndpoint::class, 'id'))->add();

        $route = $router->match($this->request('DELETE', '/endpoints'));
        $this->assertEquals('/endpoints', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'deleteList'], $route->view());
        $this->assertEquals([], $route->args());

        $route = $router->match($this->request('DELETE', '/endpoints/13'));
        $this->assertEquals('/endpoints/{id}', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'delete'], $route->view());
        $this->assertEquals(['id' => '13'], $route->args());

        $route = $router->match($this->request('GET', '/endpoints'));
        $this->assertEquals('/endpoints', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'list'], $route->view());
        $this->assertEquals([], $route->args());

        $route = $router->match($this->request('GET', '/endpoints/13'));
        $this->assertEquals('/endpoints/{id}', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'get'], $route->view());
        $this->assertEquals(['id' => '13'], $route->args());

        $route = $router->match($this->request('HEAD', '/endpoints'));
        $this->assertEquals('/endpoints', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'headList'], $route->view());
        $this->assertEquals([], $route->args());

        $route = $router->match($this->request('HEAD', '/endpoints/13'));
        $this->assertEquals('/endpoints/{id}', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'head'], $route->view());
        $this->assertEquals(['id' => '13'], $route->args());

        $route = $router->match($this->request('OPTIONS', '/endpoints'));
        $this->assertEquals('/endpoints', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'optionsList'], $route->view());
        $this->assertEquals([], $route->args());

        $route = $router->match($this->request('OPTIONS', '/endpoints/13'));
        $this->assertEquals('/endpoints/{id}', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'options'], $route->view());
        $this->assertEquals(['id' => '13'], $route->args());

        $route = $router->match($this->request('PATCH', '/endpoints/13'));
        $this->assertEquals('/endpoints/{id}', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'patch'], $route->view());
        $this->assertEquals(['id' => '13'], $route->args());

        $route = $router->match($this->request('POST', '/endpoints'));
        $this->assertEquals('/endpoints', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'post'], $route->view());
        $this->assertEquals([], $route->args());

        $route = $router->match($this->request('PUT', '/endpoints/13'));
        $this->assertEquals('/endpoints/{id}', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'put'], $route->view());
        $this->assertEquals(['id' => '13'], $route->args());
    }

    public function testEndpointWithPluralSingular(): void
    {
        $router = new Router();
        (new Endpoint($router, ['/endpoints', '/endpoint'], TestEndpoint::class, 'id'))->add();

        $route = $router->match($this->request('DELETE', '/endpoints'));
        $this->assertEquals('/endpoints', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'deleteList'], $route->view());
        $this->assertEquals([], $route->args());

        $route = $router->match($this->request('DELETE', '/endpoint/13'));
        $this->assertEquals('/endpoint/{id}', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'delete'], $route->view());
        $this->assertEquals(['id' => '13'], $route->args());

        $route = $router->match($this->request('GET', '/endpoints'));
        $this->assertEquals('/endpoints', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'list'], $route->view());
        $this->assertEquals([], $route->args());

        $route = $router->match($this->request('GET', '/endpoint/13'));
        $this->assertEquals('/endpoint/{id}', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'get'], $route->view());
        $this->assertEquals(['id' => '13'], $route->args());

        $route = $router->match($this->request('HEAD', '/endpoints'));
        $this->assertEquals('/endpoints', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'headList'], $route->view());
        $this->assertEquals([], $route->args());

        $route = $router->match($this->request('HEAD', '/endpoint/13'));
        $this->assertEquals('/endpoint/{id}', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'head'], $route->view());
        $this->assertEquals(['id' => '13'], $route->args());

        $route = $router->match($this->request('OPTIONS', '/endpoints'));
        $this->assertEquals('/endpoints', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'optionsList'], $route->view());
        $this->assertEquals([], $route->args());

        $route = $router->match($this->request('OPTIONS', '/endpoint/13'));
        $this->assertEquals('/endpoint/{id}', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'options'], $route->view());
        $this->assertEquals(['id' => '13'], $route->args());

        $route = $router->match($this->request('PATCH', '/endpoint/13'));
        $this->assertEquals('/endpoint/{id}', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'patch'], $route->view());
        $this->assertEquals(['id' => '13'], $route->args());

        $route = $router->match($this->request('POST', '/endpoints'));
        $this->assertEquals('/endpoints', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'post'], $route->view());
        $this->assertEquals([], $route->args());

        $route = $router->match($this->request('PUT', '/endpoint/13'));
        $this->assertEquals('/endpoint/{id}', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'put'], $route->view());
        $this->assertEquals(['id' => '13'], $route->args());
    }

    public function testEndpointWithName(): void
    {
        $router = new Router();
        (new Endpoint($router, '/endpoints', TestEndpoint::class, 'id'))->name('albums')->add();

        $route = $router->match($this->request('DELETE', '/endpoints'));
        $this->assertEquals('albums-deleteList', $route->name());

        $route = $router->match($this->request('DELETE', '/endpoints/13'));
        $this->assertEquals('albums-delete', $route->name());

        $route = $router->match($this->request('GET', '/endpoints'));
        $this->assertEquals('albums-list', $route->name());

        $route = $router->match($this->request('GET', '/endpoints/13'));
        $this->assertEquals('albums-get', $route->name());

        $route = $router->match($this->request('HEAD', '/endpoints'));
        $this->assertEquals('albums-headList', $route->name());

        $route = $router->match($this->request('HEAD', '/endpoints/13'));
        $this->assertEquals('albums-head', $route->name());

        $route = $router->match($this->request('OPTIONS', '/endpoints'));
        $this->assertEquals('albums-optionsList', $route->name());

        $route = $router->match($this->request('OPTIONS', '/endpoints/13'));
        $this->assertEquals('albums-options', $route->name());

        $route = $router->match($this->request('PATCH', '/endpoints/13'));
        $this->assertEquals('albums-patch', $route->name());

        $route = $router->match($this->request('POST', '/endpoints'));
        $this->assertEquals('albums-post', $route->name());

        $route = $router->match($this->request('PUT', '/endpoints/13'));
        $this->assertEquals('albums-put', $route->name());
    }

    public function testEndpointDefaultRenderer(): void
    {
        $router = new Router();
        (new Endpoint($router, '/endpoint', TestEndpoint::class, 'id'))->add();
        $route = $router->match($this->request('GET', '/endpoint'));
        $rendererConfig = $route->renderer();

        $this->assertEquals('json', $rendererConfig->type);
        $this->assertEquals([], $rendererConfig->args);
    }

    public function testEndpointSetRenderer(): void
    {
        $router = new Router();
        (new Endpoint($router, '/endpoint', TestEndpoint::class, 'id'))->render('text', 1, 'test')->add();
        $route = $router->match($this->request('GET', '/endpoint'));
        $rendererConfig = $route->renderer();

        $this->assertEquals('text', $rendererConfig->type);
        $this->assertEquals([1, 'test'], $rendererConfig->args);
    }

    public function testEndpointWithMultipleArgs(): void
    {
        $router = new Router();
        (new Endpoint($router, '/endpoints', TestEndpoint::class, ['id', 'category']))->add();

        $route = $router->match($this->request('POST', '/endpoints'));
        $this->assertEquals('/endpoints', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'post'], $route->view());
        $this->assertEquals([], $route->args());

        $route = $router->match($this->request('PUT', '/endpoints/13/albums'));
        $this->assertEquals('/endpoints/{id}/{category}', $route->pattern());
        $this->assertEquals([TestEndpoint::class, 'put'], $route->view());
        $this->assertEquals(['id' => '13', 'category' => 'albums'], $route->args());
    }
}
