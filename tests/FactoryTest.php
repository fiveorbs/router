<?php

declare(strict_types=1);

namespace Conia\Route\Tests;

use Conia\Route\Exception\RuntimeException;
use Conia\Route\Factory\Guzzle;
use Conia\Route\Factory\Laminas;
use Conia\Route\Factory\Nyholm;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use stdClass;

final class FactoryTest extends TestCase
{
    public function testNyholm(): void
    {
        $factory = new Nyholm();

        $serverRequest = $factory->serverRequest();
        $this->assertInstanceOf(\Nyholm\Psr7\ServerRequest::class, $serverRequest);

        $request = $factory->request('GET', 'http://example.com');
        $this->assertInstanceOf(\Nyholm\Psr7\Request::class, $request);

        $response = $factory->response();
        $this->assertInstanceOf(\Nyholm\Psr7\Response::class, $response);

        $stream = $factory->stream();
        $this->assertInstanceOf(\Nyholm\Psr7\Stream::class, $stream);

        $stream = $factory->stream(fopen('php://temp', 'r+'));
        $this->assertInstanceOf(\Nyholm\Psr7\Stream::class, $stream);

        $stream = $factory->streamFromFile(__DIR__ . '/Fixtures/image.webp');
        $this->assertInstanceOf(\Nyholm\Psr7\Stream::class, $stream);

        $uri = $factory->uri('http://example.com');
        $this->assertInstanceOf(\Nyholm\Psr7\Uri::class, $uri);

        $uploadedFile = $factory->uploadedFile($stream);
        $this->assertInstanceOf(\Nyholm\Psr7\UploadedFile::class, $uploadedFile);

        $this->assertInstanceOf(RequestFactoryInterface::class, $factory->requestFactory);
        $this->assertInstanceOf(ServerRequestFactoryInterface::class, $factory->serverRequestFactory);
        $this->assertInstanceOf(ResponseFactoryInterface::class, $factory->responseFactory);
        $this->assertInstanceOf(StreamFactoryInterface::class, $factory->streamFactory);
        $this->assertInstanceOf(UploadedFileFactoryInterface::class, $factory->uploadedFileFactory);
        $this->assertInstanceOf(UriFactoryInterface::class, $factory->uriFactory);
    }

    public function testNyholmStreamFailing(): void
    {
        $this->throws(RuntimeException::class, 'Only strings');

        (new Nyholm())->stream(new stdClass());
    }

    public function testGuzzle(): void
    {
        $factory = new Guzzle();

        $serverRequest = $factory->serverRequest();
        $this->assertInstanceOf(\GuzzleHttp\Psr7\ServerRequest::class, $serverRequest);

        $request = $factory->request('GET', 'http://example.com');
        $this->assertInstanceOf(\GuzzleHttp\Psr7\Request::class, $request);

        $response = $factory->response();
        $this->assertInstanceOf(\GuzzleHttp\Psr7\Response::class, $response);

        $stream = $factory->stream();
        $this->assertInstanceOf(\GuzzleHttp\Psr7\Stream::class, $stream);

        $stream = $factory->stream(fopen('php://temp', 'r+'));
        $this->assertInstanceOf(\GuzzleHttp\Psr7\Stream::class, $stream);

        $stream = $factory->streamFromFile(__DIR__ . '/Fixtures/image.webp');
        $this->assertInstanceOf(\GuzzleHttp\Psr7\Stream::class, $stream);

        $uri = $factory->uri('http://example.com');
        $this->assertInstanceOf(\GuzzleHttp\Psr7\Uri::class, $uri);

        $uploadedFile = $factory->uploadedFile($stream);
        $this->assertInstanceOf(\GuzzleHttp\Psr7\UploadedFile::class, $uploadedFile);

        $this->assertInstanceOf(RequestFactoryInterface::class, $factory->requestFactory);
        $this->assertInstanceOf(ServerRequestFactoryInterface::class, $factory->serverRequestFactory);
        $this->assertInstanceOf(ResponseFactoryInterface::class, $factory->responseFactory);
        $this->assertInstanceOf(StreamFactoryInterface::class, $factory->streamFactory);
        $this->assertInstanceOf(UploadedFileFactoryInterface::class, $factory->uploadedFileFactory);
        $this->assertInstanceOf(UriFactoryInterface::class, $factory->uriFactory);
    }

    public function testGuzzleStreamFailing(): void
    {
        $this->throws(RuntimeException::class, 'Only strings');

        (new Guzzle())->stream(new stdClass());
    }

    public function testLaminas(): void
    {
        $factory = new Laminas();

        $serverRequest = $factory->serverRequest();
        $this->assertInstanceOf(\Laminas\Diactoros\ServerRequest::class, $serverRequest);

        $request = $factory->request('GET', 'http://example.com');
        $this->assertInstanceOf(\Laminas\Diactoros\Request::class, $request);

        $response = $factory->response();
        $this->assertInstanceOf(\Laminas\Diactoros\Response::class, $response);

        $stream = $factory->stream();
        $this->assertInstanceOf(\Laminas\Diactoros\Stream::class, $stream);

        $stream = $factory->stream(fopen('php://temp', 'r+'));
        $this->assertInstanceOf(\Laminas\Diactoros\Stream::class, $stream);

        $stream = $factory->streamFromFile(__DIR__ . '/Fixtures/image.webp');
        $this->assertInstanceOf(\Laminas\Diactoros\Stream::class, $stream);

        $uri = $factory->uri('http://example.com');
        $this->assertInstanceOf(\Laminas\Diactoros\Uri::class, $uri);

        $uploadedFile = $factory->uploadedFile($stream);
        $this->assertInstanceOf(\Laminas\Diactoros\UploadedFile::class, $uploadedFile);

        $this->assertInstanceOf(RequestFactoryInterface::class, $factory->requestFactory);
        $this->assertInstanceOf(ServerRequestFactoryInterface::class, $factory->serverRequestFactory);
        $this->assertInstanceOf(ResponseFactoryInterface::class, $factory->responseFactory);
        $this->assertInstanceOf(StreamFactoryInterface::class, $factory->streamFactory);
        $this->assertInstanceOf(UploadedFileFactoryInterface::class, $factory->uploadedFileFactory);
        $this->assertInstanceOf(UriFactoryInterface::class, $factory->uriFactory);
    }

    public function testLaminasStreamFailing(): void
    {
        $this->throws(RuntimeException::class, 'Only strings');

        (new Laminas())->stream(new stdClass());
    }
}
