<?php

declare(strict_types=1);

namespace Conia\Route\Renderer;

use Conia\Route\Factory;
use Psr\Http\Message\ResponseInterface as Response;

/** @psalm-api */
class HtmlRenderer implements Renderer
{
    public function __construct(protected Factory $factory)
    {
    }

    public function render(mixed $data, mixed ...$args): string
    {
        return (string)$data;
    }

    public function response(mixed $data, mixed ...$args): Response
    {
        return $this->factory->response()
            ->withHeader('Content-Type', 'text/html')
            ->withBody($this->factory->stream($this->render($data, ...$args)));
    }
}
