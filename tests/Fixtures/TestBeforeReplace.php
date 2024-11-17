<?php

declare(strict_types=1);

namespace FiveOrbs\Router\Tests\Fixtures;

use FiveOrbs\Router\Before;
use Psr\Http\Message\ServerRequestInterface as Request;

class TestBeforeReplace implements Before
{
	public function handle(Request $request): Request
	{
		return $request
			->withAttribute('first', 'replaced');
	}

	public function replace(Before $handler): bool
	{
		return false;
	}
}
