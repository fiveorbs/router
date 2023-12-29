<?php

declare(strict_types=1);

namespace Conia\Route\Tests\Fixtures;

class TestInvokableClass
{
    public function __invoke()
    {
        return 'Invokable';
    }
}
