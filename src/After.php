<?php

declare(strict_types=1);

namespace FiveOrbs\Router;

interface After
{
	public function handle(mixed $data): mixed;

	public function replace(After $handler): bool;
}
