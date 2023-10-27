<?php

declare(strict_types=1);

namespace Conia\Route\Tests;

use Conia\Route\Exception\RuntimeException;
use Conia\Route\Factory\Guzzle;
use Conia\Route\Factory\Laminas;
use Conia\Route\Factory\Nyholm;
use stdClass;

final class FactoryTest extends TestCase
{
    public function testNyholm(): void
    {
        $factory = new Nyholm();

        $request = $factory->request();
        $this->assertInstanceOf(\Nyholm\Psr7\ServerRequest::class, $request);

        $response = $factory->response();
        $this->assertInstanceOf(\Nyholm\Psr7\Response::class, $response);

        $stream = $factory->stream();
        $this->assertInstanceOf(\Nyholm\Psr7\Stream::class, $stream);

        $stream = $factory->stream(fopen('php://temp', 'r+'));
        $this->assertInstanceOf(\Nyholm\Psr7\Stream::class, $stream);

        $stream = $factory->streamFromFile(__DIR__ . '/Fixtures/image.webp');
        $this->assertInstanceOf(\Nyholm\Psr7\Stream::class, $stream);
    }

    public function testNyholmStreamFailing(): void
    {
        $this->throws(RuntimeException::class, 'Only strings');

        (new Nyholm())->stream(new stdClass());
    }

    public function testGuzzle(): void
    {
        $factory = new Guzzle();

        $request = $factory->request();
        $this->assertInstanceOf(\GuzzleHttp\Psr7\ServerRequest::class, $request);

        $response = $factory->response();
        $this->assertInstanceOf(\GuzzleHttp\Psr7\Response::class, $response);

        $stream = $factory->stream();
        $this->assertInstanceOf(\GuzzleHttp\Psr7\Stream::class, $stream);

        $stream = $factory->stream(fopen('php://temp', 'r+'));
        $this->assertInstanceOf(\GuzzleHttp\Psr7\Stream::class, $stream);

        $stream = $factory->streamFromFile(__DIR__ . '/Fixtures/image.webp');
        $this->assertInstanceOf(\GuzzleHttp\Psr7\Stream::class, $stream);
    }

    public function testGuzzleStreamFailing(): void
    {
        $this->throws(RuntimeException::class, 'Only strings');

        (new Guzzle())->stream(new stdClass());
    }

    public function testLaminas(): void
    {
        $factory = new Laminas();

        $request = $factory->request();
        $this->assertInstanceOf(\Laminas\Diactoros\ServerRequest::class, $request);

        $response = $factory->response();
        $this->assertInstanceOf(\Laminas\Diactoros\Response::class, $response);

        $stream = $factory->stream();
        $this->assertInstanceOf(\Laminas\Diactoros\Stream::class, $stream);

        $stream = $factory->stream(fopen('php://temp', 'r+'));
        $this->assertInstanceOf(\Laminas\Diactoros\Stream::class, $stream);

        $stream = $factory->streamFromFile(__DIR__ . '/Fixtures/image.webp');
        $this->assertInstanceOf(\Laminas\Diactoros\Stream::class, $stream);
    }

    public function testLaminasStreamFailing(): void
    {
        $this->throws(RuntimeException::class, 'Only strings');

        (new Laminas())->stream(new stdClass());
    }
}
