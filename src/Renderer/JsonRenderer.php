<?php

declare(strict_types=1);

namespace Conia\Route\Renderer;

use Psr\Http\Message\ResponseFactoryInterface as Factory;
use Psr\Http\Message\ResponseInterface as Response;
use Traversable;

/** @psalm-api */
class JsonRenderer implements Renderer
{
    public function __construct(protected Factory $factory)
    {
    }

    public function render(mixed $data, mixed ...$args): string
    {
        $flags = JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR;

        if (count($args) > 0) {
            /** @var mixed */
            $arg = $args[array_key_first($args)];

            if (is_int($arg)) {
                $flags = $arg;
            }
        }

        if ($data instanceof Traversable) {
            return json_encode(iterator_to_array($data), $flags);
        }

        return json_encode($data, $flags);
    }

    public function response(mixed $data, mixed ...$args): Response
    {
        $response = $this->factory->createResponse()
            ->withHeader('Content-Type', 'application/json');
        $response->getBody()->write($this->render($data, ...$args));

        return $response;
    }
}
