<?php

declare(strict_types=1);

namespace Conia\Route\Renderer;

use Attribute;

/** @psalm-api */
#[Attribute]
class Render
{
    protected array $args;

    public function __construct(protected string $renderer, mixed ...$args)
    {
        $this->args = $args;
    }
}
