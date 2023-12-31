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
    use AddsBeforeAfter;
    use AddsMiddleware;

    public function dispatch(Request $request, Route $route, ?Container $container = null): Response
    {
        $view = new View($route, $container);
        $view->setBeforeHandlers($this->beforeHandlers);
        $view->setAfterHandlers($this->afterHandlers);

        $handler = new ViewHandler($view, $this->middleware);

        return $handler->handle($request);
    }
}
