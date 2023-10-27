<?php

declare(strict_types=1);

namespace Conia\Route\Tests\Fixtures;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as Handler;

class TestMiddleware2 implements Middleware
{
    public function process(Request $request, Handler $handler): Response
    {
        return $handler->handle($request);
    }
}
