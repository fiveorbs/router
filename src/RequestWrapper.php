<?php

declare(strict_types=1);

namespace Conia\Route;

use Psr\Http\Message\ServerRequestInterface as Request;

interface RequestWrapper
{
    public function unwrap(): Request;
}
