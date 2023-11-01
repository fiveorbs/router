<?php

declare(strict_types=1);

namespace Conia\Route\Tests\Fixtures;

use Conia\Route\Renderer\Renderer;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;

class TestRendererArgsOptions implements Renderer
{
    public function __construct(
        protected ResponseFactoryInterface $responseFactory,
        protected int $option1,
        protected string $option2,
    ) {
    }

    public function render(mixed $data, mixed ...$args): string
    {
        return print_r($this->prepareData($data, $args), return: true);
    }

    public function response(mixed $data, mixed ...$args): Response
    {
        $data = $this->prepareData($data, $args);
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

    private function prepareData(mixed $data, array $args): mixed
    {
        if (is_array($data)) {
            $data = array_merge($data, $args);
        }

        if (is_array($data)) {
            $data = array_merge($data, ['option1' => $this->option1, 'option2' => $this->option2]);
        }

        return $data;
    }
}
