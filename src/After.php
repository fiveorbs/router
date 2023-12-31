<?php

declare(strict_types=1);

namespace Conia\Route;

use Psr\Http\Message\ResponseInterface as Response;

interface After
{
    public function handle(mixed $request): Response;

    public function replace(): bool;
}
