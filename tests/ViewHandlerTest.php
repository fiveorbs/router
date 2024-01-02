<?php

declare(strict_types=1);

namespace Conia\Route\Tests;

use Conia\Route\Exception\RuntimeException;
use Conia\Route\Route;
use Conia\Route\Tests\Fixtures\TestController;
use Conia\Route\View;
use Conia\Route\ViewHandler;

class ViewHandlerTest extends TestCase
{
    public function testViewResponse(): void
    {
        $route = new Route('/', function () {
            $response = $this->responseFactory()->createResponse()
                ->withHeader('Content-Type', 'text/plain');
            $response->getBody()->write('Conia PSR Response');

            return $response;
        });
        $route->match('/');
        $view = new View($route, null);
        $handler = new ViewHandler($view, [], []);
        $response = $handler->handle($this->request());

        $this->assertSame('Conia PSR Response', (string)$response->getBody());
        $this->assertSame('text/plain', $response->getHeaders()['Content-Type'][0]);
    }

    public function testWrongViewReturnType(): void
    {
        $this->throws(RuntimeException::class, 'Unable to determine a response handler');

        $route = new Route('/', TestController::class . '::wrongReturnType');
        $view = new View($route, null);
        $handler = new ViewHandler($view, [], []);
        $handler->handle($this->request());
    }
}
