<?php

declare(strict_types=1);

namespace Conia\Route\Tests\Fixtures;

use Conia\Route\After;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;

class TestRendererArgsOptions implements After
{
    public function __construct(
        protected ResponseFactoryInterface $responseFactory,
        protected int $option1,
        protected string $option2,
    ) {
    }

    public function handle(mixed $data): Response
    {
        $response = $this->responseFactory->createResponse();

        if (is_array($data)) {
            $response = $response->withHeader('Content-Type', 'application/json');
            $flags = JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR;
            $response->getBody()->write(json_encode($data, $flags));
        } else {
            $response = $response->withHeader('Content-Type', 'text/plain');
            $response->getBody()->write((string)$data);
        }

        return $response;
    }
}
