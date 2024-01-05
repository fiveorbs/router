<?php

declare(strict_types=1);

namespace Conia\Route\Tests\Fixtures;

use Conia\Route\Before;
use Psr\Http\Message\ServerRequestInterface as Request;

class TestBeforeThird implements Before
{
    public function handle(Request $request): Request
    {
        return $request->withAttribute('third', 'third-value');
    }

    public function replace(Before $handler): bool
    {
        return false;
    }
}
