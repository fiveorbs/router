<?php

declare(strict_types=1);

namespace Conia\Route\Tests\Fixtures;

use Conia\Route\Before;
use Psr\Http\Message\ServerRequestInterface as Request;

class TestBeforeSecond implements Before
{
    public function handle(Request $request): Request
    {
        return $request
            ->withAttribute('first', $request->getAttribute('first', '') . '-added-second-value')
            ->withAttribute('second', 'second-value');
    }
}
