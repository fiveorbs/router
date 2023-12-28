<?php

declare(strict_types=1);

namespace Conia\Route;

use Conia\Route\Exception\RuntimeException;
use Conia\Route\Renderer\Render;
use Conia\Route\Renderer\Renderer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class ViewHandler implements RequestHandler
{
    /** @param list<Middleware> $middleware */
    protected array $middleware;

    /**
     * @param array<string, Renderer> $renderers
     * @param list<Middleware> $renderers
     */
    public function __construct(
        protected readonly View $view,
        protected readonly array $renderers,
        array $globalMiddleware,
    ) {
        $this->middleware = array_merge($globalMiddleware, $view->middleware());
    }

    public function handle(Request $request): Response
    {
        if (0 === count($this->middleware)) {
            return $this->execute($request);
        }

        $middleware = array_shift($this->middleware);

        return $middleware->process($request, $this);
    }

    protected function execute(Request $request): Response
    {
        /**
         * @psalm-suppress MixedAssignment
         *
         * Type checking takes place in the renderers code.
         */
        $data = $this->view->execute($request);

        if ($data instanceof Response) {
            return $data;
        }

        $renderAttributes = $this->view->attributes(Render::class);

        if (count($renderAttributes) > 0) {
            assert($renderAttributes[0] instanceof Render);

            return $this->respondFromRenderer($data, $renderAttributes[0]->type, $renderAttributes[0]->args);
        }

        $rendererConfig = $this->view->renderer();

        if ($rendererConfig) {
            return $this->respondFromRenderer($data, $rendererConfig->type, $rendererConfig->args);
        }

        if (is_object($data)) {
            $type = $data::class;
        } else {
            $type = gettype($data);
        }

        if (isset($this->renderers[$type])) {
            return $this->respondFromRenderer($data, $type, []);
        }

        throw new RuntimeException('Unable to determine a response handler for the returned value of the view');
    }

    protected function respondFromRenderer(
        mixed $data,
        string $rendererType,
        array $args,
    ): Response {
        $renderer = $this->renderers[$rendererType];

        return $renderer->response($data, ...$args);
    }
}
