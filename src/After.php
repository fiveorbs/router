<?php

declare(strict_types=1);

namespace Conia\Route;

interface After
{
    public function handle(mixed $data): mixed;

    public function replace(After $handler): bool;
}
