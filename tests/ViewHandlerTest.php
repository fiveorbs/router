<?php

declare(strict_types=1);

namespace Conia\Route\Tests;

use Conia\Route\Route;
use Conia\Route\Tests\Fixtures\TestRendererArgsOptions;
use Conia\Route\View;
use Conia\Route\ViewHandler;

class ViewHandlerTest extends TestCase
{
    public function testViewResponse(): void
    {
        $route = new Route('/', function () {
            $response = $this->responseFactory()->createResponse()
                ->withHeader('Content-Type', 'text/plain');
            $response->getBody()->write('Chuck PSR Response');

            return $response;
        });
        $route->match('/');
        $view = new View($route, null);
        $handler = new ViewHandler($view, [], []);
        $response = $handler->handle($this->request());

        $this->assertEquals('Chuck PSR Response', (string)$response->getBody());
        $this->assertEquals('text/plain', $response->getHeaders()['Content-Type'][0]);
    }

    public function testViewResponseRendererWithArgsAndOptions(): void
    {
        $route = (new Route('/', fn () => ['name' => 'Chuck']))
            ->render('test', arg1: 'Arg', arg2: 73);
        $route->match('/');
        $view = new View($route, null);
        $handler = new ViewHandler($view, [
            'test' => new TestRendererArgsOptions($this->responseFactory(), 13, 'Option'),
        ], []);
        $response = $handler->handle($this->request());

        $this->assertEquals('{"name":"Chuck","arg1":"Arg","arg2":73,"option1":13,"option2":"Option"}', (string)$response->getBody());
        $this->assertEquals('application/json', $response->getHeaders()['Content-Type'][0]);
    }

    public function testViewResponseRendererWithOptionsClosure(): void
    {
        $route = (new Route('/', fn () => ['name' => 'Chuck']))->render('test');
        $route->match('/');
        $view = new View($route, null);
        $handler = new ViewHandler($view, [
            'test' => new TestRendererArgsOptions($this->responseFactory(), 13, 'Option'),
        ], []);
        $response = $handler->handle($this->request());

        $this->assertEquals('{"name":"Chuck","option1":13,"option2":"Option"}', (string)$response->getBody());
        $this->assertEquals('application/json', $response->getHeaders()['Content-Type'][0]);
    }
}
