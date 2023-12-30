<?php

declare(strict_types=1);

namespace Conia\Route;

use Conia\Route\View;
use Conia\Route\ViewHandler;
use Psr\Container\ContainerInterface as Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Dispatcher
{
    use AddsMiddleware;

    /** @param list<Before> */
    protected array $beforeHandlers = [];

    /** @param list<After> */
    protected array $afterHandlers = [];

    public function before(Before $before): void
    {
        $this->beforeHandlers[] = $before;
    }

    /** @return list<Before> */
    public function beforeHandlers(): array
    {
        return $this->beforeHandlers;
    }

    public function after(After $after): void
    {
        $this->afterHandlers[] = $after;
    }

    /** @return list<After> */
    public function afterHandlers(): array
    {
        return $this->afterHandlers;
    }

    public function dispatch(Request $request, Route $route, ?Container $container = null): Response
    {
        $handler = new ViewHandler(new View($route, $container), $this->middleware);

        return $handler->handle($request);
    }
}
