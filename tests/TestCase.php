<?php

declare(strict_types=1);

namespace Conia\Route\Tests;

use Conia\Registry\Registry;
use Conia\Route\Renderer\HtmlRenderer;
use Conia\Route\Renderer\JsonRenderer;
use Conia\Route\Renderer\Renderer;
use Conia\Route\Renderer\TextRenderer;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\Uri;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\Http\Message\ResponseFactoryInterface;
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
        $registry->add(ResponseFactoryInterface::class, $this->responseFactory());

        $rendererTag = $registry->tag(Renderer::class);
        $rendererTag->add('text', TextRenderer::class);
        $rendererTag->add('json', JsonRenderer::class);
        $rendererTag->add('html', HtmlRenderer::class);

        return $registry;
    }

    public function responseFactory(): ResponseFactory
    {
        return new ResponseFactory();
    }

    public function fullTrim(string $text): string
    {
        return trim(
            preg_replace(
                '/> </',
                '><',
                preg_replace(
                    '/\s+/',
                    ' ',
                    preg_replace('/\n/', '', $text)
                )
            )
        );
    }
}
