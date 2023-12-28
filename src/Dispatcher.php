<?php

declare(strict_types=1);

namespace Conia\Route;

use Conia\Route\Renderer\Renderer;
use Conia\Route\View;
use Conia\Route\ViewHandler;
use Psr\Container\ContainerInterface as Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Dispatcher
{
    use AddsMiddleware;

    /** @param array<string, Renderer> $renderers */
    protected array $renderers = [];

    public function renderer(string $key, Renderer $renderer): void
    {
        $this->renderers[$key] = $renderer;
    }

    public function dispatch(Request $request, Route $route, ?Container $container = null): Response
    {
        $handler = new ViewHandler(new View($route, $container), $this->middleware, $this->renderers);

        return $handler->handle($request);
    }
}
