<?php

declare(strict_types=1);

namespace Conia\Route\Tests;

use Conia\Route\Factory;
use Conia\Route\Factory\Nyholm;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    public function throws(string $exception, string $message = null): void
    {
        $this->expectException($exception);

        if ($message) {
            $this->expectExceptionMessage($message);
        }
    }

    public function factory(): Factory
    {
        return new Nyholm();
    }
}
