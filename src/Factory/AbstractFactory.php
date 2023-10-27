<?php

declare(strict_types=1);

namespace Conia\Route\Factory;

use Conia\Route\Exception\RuntimeException;
use Conia\Route\Factory;
use Psr\Http\Message\RequestFactoryInterface as Requestfactory;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseFactoryInterface as ResponseFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestFactoryInterface as ServerRequestFactory;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Http\Message\StreamFactoryInterface as StreamFactory;
use Psr\Http\Message\StreamInterface as Stream;
use Psr\Http\Message\UploadedFileFactoryInterface as UploadedFileFactory;
use Psr\Http\Message\UploadedFileInterface as UploadedFile;
use Psr\Http\Message\UriFactoryInterface as UriFactory;
use Psr\Http\Message\UriInterface as Uri;
use Stringable;

/** @psalm-api */
abstract class AbstractFactory implements Factory
{
    public readonly RequestFactory $requestFactory;
    public readonly ResponseFactory $responseFactory;
    public readonly ServerRequestFactory $serverRequestFactory;
    public readonly StreamFactory $streamFactory;
    public readonly UploadedFileFactory $uploadedFileFactory;
    public readonly UriFactory $uriFactory;

    abstract public function serverRequest(): ServerRequest;

    public function request(string $method, Uri|string $uri): Request
    {
        return $this->requestFactory->createRequest($method, $uri);
    }

    public function response(int $code = 200, string $reasonPhrase = '', string|Stream $body = null): Response
    {
        $response =  $this->responseFactory->createResponse($code, $reasonPhrase);

        if (!is_null($body)) {
            if (is_string($body)) {
                $response = $response->withBody($this->streamFactory->createStream($body));
            } else {
                $response = $response->withBody($body);
            }
        }

        return $response;
    }

    public function stream(mixed $content = ''): Stream
    {
        if (is_string($content) || $content instanceof Stringable) {
            return $this->streamFactory->createStream((string)$content);
        }

        if (is_resource($content)) {
            return $this->streamFactory->createStreamFromResource($content);
        }

        throw new RuntimeException('Only strings, Stringable or resources are allowed');
    }

    public function streamFromFile(string $filename, string $mode = 'r'): Stream
    {
        return $this->streamFactory->createStreamFromFile($filename, $mode);
    }

    public function uploadedFile(
        Stream $stream,
        int $size = null,
        int $error = \UPLOAD_ERR_OK,
        string $clientFilename = null,
        string $clientMediaType = null
    ): UploadedFile {
        return $this->uploadedFileFactory->createUploadedFile($stream, $size, $error, $clientFilename, $clientMediaType);
    }

    public function uri(string $uri = ''): Uri
    {
        return $this->uriFactory->createUri($uri);
    }

    protected function setResponseFactory(ResponseFactory $factory): void
    {
        $this->responseFactory = $factory;
    }

    protected function setRequestFactory(RequestFactory $factory): void
    {
        $this->requestFactory = $factory;
    }

    protected function setStreamFactory(StreamFactory $factory): void
    {
        $this->streamFactory = $factory;
    }

    protected function setServerRequestFactory(ServerRequestFactory $factory): void
    {
        $this->serverRequestFactory = $factory;
    }

    protected function setUploadedFileFactory(UploadedFileFactory $factory): void
    {
        $this->uploadedFileFactory = $factory;
    }

    protected function setUriFactory(UriFactory $factory): void
    {
        $this->uriFactory = $factory;
    }
}
