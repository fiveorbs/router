<?php

declare(strict_types=1);

namespace FiveOrbs\Router\Tests\Fixtures;

use FiveOrbs\Router\Route;

class TestControllerWithRoute
{
	public function __construct(protected Route $route) {}

	public function routeOnly(): string
	{
		return $this->route::class;
	}
}
