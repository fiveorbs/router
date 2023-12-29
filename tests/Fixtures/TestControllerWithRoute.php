<?php

declare(strict_types=1);

namespace Conia\Route\Tests\Fixtures;

use Conia\Route\Route;

class TestControllerWithRoute
{
    public function __construct(protected Route $route)
    {
    }

    public function routeOnly(): Route
    {
        return $this->route;
    }
}
