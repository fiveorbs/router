<?php

declare(strict_types=1);

namespace Conia\Route\Tests;

use Conia\Registry\Registry;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\Uri;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\Http\Message\ServerRequestInterface as Request;

class TestCase extends BaseTestCase
{
    public function throws(string $exception, string $message = null): void
    {
        $this->expectException($exception);

        if ($message) {
            $this->expectExceptionMessage($message);
        }
    }

    public function request(
        ?string $method = null,
        ?string $uri = null,
    ): Request {
        $request = ServerRequestFactory::fromGlobals();

        if ($method) {
            $request = $request->withMethod($method);
        }

        if ($uri) {
            $request = $request->withUri(new Uri($uri));
        }

        return $request;
    }

    public function registry(
        bool $autowire = true,
    ): Registry {
        $registry = new Registry(autowire: $autowire);

        return $registry;
    }

    public function responseFactory(): ResponseFactory
    {
        return new ResponseFactory();
    }
}
