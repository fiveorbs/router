<?php

declare(strict_types=1);

namespace Conia\Route;

use Conia\Route\Exception\RuntimeException;
use Conia\Route\Renderer\Render;
use Conia\Route\Route;
use Psr\Http\Message\ResponseInterface as Response;

class ViewHandler
{
    public function __construct(
        protected readonly View $view,
        protected readonly Route $route,
        /** @param list<Conia\Route\Renderer\Renderer> */
        protected readonly array $renderers,
    ) {
    }

    public function __invoke(): Response
    {
        /**
         * @psalm-suppress MixedAssignment
         *
         * Type checking takes place in the renderers code.
         */
        $data = $this->view->execute();

        if ($data instanceof Response) {
            return $data;
        }

        $renderAttributes = $this->view->attributes(Render::class);

        if (count($renderAttributes) > 0) {
            assert($renderAttributes[0] instanceof Render);

            return $this->respondFromRenderer($data, $renderAttributes[0]->type, $renderAttributes[0]->args);
        }

        $rendererConfig = $this->route->getRenderer();

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
