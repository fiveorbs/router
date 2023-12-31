<?php

declare(strict_types=1);

namespace Conia\Route\Tests;

use Conia\Route\Dispatcher;
use Conia\Route\Route;
use Conia\Route\Tests\Fixtures\TestAfterAddText;
use Conia\Route\Tests\Fixtures\TestAfterRenderer;
use Conia\Route\Tests\Fixtures\TestMiddleware1;
use Conia\Route\Tests\Fixtures\TestMiddleware2;
use Psr\Http\Message\ResponseInterface as Response;

class DispatcherTest extends TestCase
{
    public function testDispatchClosure(): void
    {
        $route = new Route(
            '/',
            function () {
                $response = $this->responseFactory()->createResponse()->withHeader('Content-Type', 'text/html');
                $response->getBody()->write('Conia');

                return $response;
            }
        );
        $dispatcher = new Dispatcher();
        $response = $dispatcher->dispatch($this->request('GET', '/'), $route);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('Conia', (string)$response->getBody());
    }

    public function testAddMiddleware(): void
    {
        $dispatcher = new Dispatcher();

        $dispatcher->middleware(new TestMiddleware1());
        $dispatcher->middleware(new TestMiddleware2());

        $this->assertEquals(2, count($dispatcher->getMiddleware()));
    }

    public function testAddRenderers(): void
    {
        $dispatcher = new Dispatcher();

        $dispatcher->after(new TestAfterRenderer($this->responseFactory()));
        $dispatcher->after(new TestAfterAddText());

        $this->assertEquals(2, count($dispatcher->afterHandlers()));
        $this->assertInstanceof(TestAfterRenderer::class, $dispatcher->afterHandlers()[0]);
        $this->assertInstanceof(TestAfterAddText::class, $dispatcher->afterHandlers()[1]);
    }
}
