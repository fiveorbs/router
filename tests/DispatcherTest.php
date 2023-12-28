<?php

declare(strict_types=1);

namespace Conia\Route\Tests;

use Conia\Route\Dispatcher;
use Conia\Route\Route;
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
}
