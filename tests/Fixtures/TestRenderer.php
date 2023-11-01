<?php

declare(strict_types=1);

namespace Conia\Route\Tests\Fixtures;

use Conia\Route\Renderer\Renderer;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;

class TestRenderer implements Renderer
{
    public function __construct(protected ResponseFactoryInterface $responseFactory)
    {
    }

    public function render(mixed $data, mixed ...$args): string
    {
        return print_r($data, return: true);
    }

    public function response(mixed $data, mixed ...$args): Response
    {
        $response = $this->responseFactory->createResponse()->withHeader('Content-Type', 'text/plain');
        $response->getBody()->write($this->render($data));

        return $response;
    }
}
