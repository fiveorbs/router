<?php

declare(strict_types=1);

namespace Conia\Route\Renderer;

use Psr\Http\Message\ResponseInterface as Response;

interface Renderer
{
    public function render(mixed $data, mixed ...$args): string;

    public function response(mixed $data, mixed ...$args): Response;
}
