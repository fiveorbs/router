<?php

declare(strict_types=1);

namespace Conia\Route\Renderer;

use Attribute;

/** @psalm-api */
#[Attribute]
class Render
{
    public readonly array $args;

    public function __construct(public readonly string $type, mixed ...$args)
    {
        $this->args = $args;
    }
}
