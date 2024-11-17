<?php

declare(strict_types=1);

namespace FiveOrbs\Router\Tests\Fixtures;

class TestInvokableClass
{
	public function __invoke()
	{
		return 'Invokable';
	}
}
