<?php

declare(strict_types=1);

namespace Conia\Route\Tests\Fixtures;

use Conia\Route\Factory;
use Conia\Route\Renderer\Render;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TestController
{
    #[TestAttribute, Render('text')]
    public function textView(): string
    {
        return 'text';
    }

    public function stringableView(): TestClass
    {
        return new TestClass();
    }

    #[TestAttribute, TestAttributeExt, TestAttributeDiff]
    public function arrayView(): array
    {
        return ['success' => true];
    }

    public function middlewareView(Factory $factory): Response
    {
        return $factory->response(body: ' view')->withHeader('Content-Type', 'text/html');
    }

    #[Render('text'), TestMiddleware1]
    public function attributedMiddlewareView(Factory $factory): Response
    {
        $s = ' attribute-string';

        return $factory->response(body: $s)->withHeader('Content-Type', 'text/html');
    }

    public function routeParams(string $string, float $float, Request $request, int $int): array
    {
        return [
            'string' => $string,
            'float' => $float,
            'int' => $int,
            'request' => $request::class,
        ];
    }

    public function routeDefaultValueParams(string $string, int $int = 13): array
    {
        return [
            'string' => $string,
            'int' => $int,
        ];
    }
}
