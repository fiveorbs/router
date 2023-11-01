<?php

declare(strict_types=1);

namespace Conia\Route\Renderer;

use Psr\Http\Message\ResponseFactoryInterface as Factory;
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
        $response = $this->factory->createResponse()
            ->withHeader('Content-Type', 'text/html');
        $response->getBody()->write($this->render($data, ...$args));

        return $response;
    }
}
