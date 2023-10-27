<?php

declare(strict_types=1);

namespace Conia\Route;

use Conia\Registry\Registry;
use Conia\Route\Factory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class Dispatcher
{
    protected Factory $factory;

    public function __construct(
        protected readonly array $queue,
        Registry $registry
    ) {
        $factory = $registry->get(Factory::class);
        assert($factory instanceof Factory);
        $this->factory = $factory;
    }

    /**
     * Recursively calls the callables in the middleware/view handler queue
     * and then the view callable.
     */
    public function handle(array $queue, Request $request): Response
    {
        /** @psalm-var non-empty-list<Middleware|PsrMiddleware|ViewHandler> $queue */
        $handler = $queue[0];

        if ($handler instanceof Middleware) {
            return $handler->process(
                $request->psr(),
                // Create an anonymous PSR-15 RequestHandler
                new class ($this, array_slice($queue, 1)) implements RequestHandler {
                    public function __construct(
                        protected readonly Dispatcher $dispatcher,
                        protected readonly array $queue
                    ) {
                    }

                    public function handle(ServerRequest $request): PsrResponse
                    {
                        return $this->dispatcher->handle($this->queue, new Request($request));
                    }
                }
            );
        }

        return $handler();
    }

    public function dispatch(
        Request $request,
    ): PsrResponse {
        return $this->handle($this->queue, $request);
    }
}
