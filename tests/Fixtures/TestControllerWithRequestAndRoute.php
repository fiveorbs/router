<?php

declare(strict_types=1);

namespace Conia\Route\Tests\Fixtures;

use Conia\Route\Route;
use Psr\Http\Message\RequestInterface as Request;

class TestControllerWithRequestAndRoute
{
    public function __construct(
        protected Route $route,
        protected Request $request,
        protected string $param,
    ) {
    }

    public function requestAndRoute(): array
    {
        return [$this->request, $this->route, $this->param];
    }
}
