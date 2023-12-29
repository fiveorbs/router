<?php

declare(strict_types=1);

namespace Conia\Route\Tests\Fixtures;

use Conia\Route\Renderer\Render;
use Psr\Http\Message\RequestInterface as Request;

class TestControllerWithRequest
{
    public function __construct(protected Request $request)
    {
    }

    #[Render('text')]
    public function requestOnlyRendered(): string
    {
        return $this->request::class;
    }

    public function requestOnly(): Request
    {
        return $this->request;
    }

    public function routeParams(string $string, float $float, int $int): array
    {
        return [
            'string' => $string,
            'float' => $float,
            'int' => $int,
            'request' => $this->request::class,
        ];
    }
}
