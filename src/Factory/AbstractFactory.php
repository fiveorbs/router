<?php

declare(strict_types=1);

namespace Conia\Route\Factory;

use Conia\Route\Exception\RuntimeException;
use Conia\Route\Factory;
use Psr\Http\Message\RequestFactoryInterface as PsrRequestfactory;
use Psr\Http\Message\RequestInterface as PsrRequest;
use Psr\Http\Message\ResponseFactoryInterface as PsrResponseFactory;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use Psr\Http\Message\ServerRequestFactoryInterface as PsrServerRequestFactory;
use Psr\Http\Message\ServerRequestInterface as PsrServerRequest;
use Psr\Http\Message\StreamFactoryInterface as PsrStreamFactory;
use Psr\Http\Message\StreamInterface as PsrStream;
use Psr\Http\Message\UploadedFileFactoryInterface as PsrUploadedFileFactory;
use Psr\Http\Message\UploadedFileInterface as PsrUploadedFile;
use Psr\Http\Message\UriFactoryInterface as PsrUriFactory;
use Psr\Http\Message\UriInterface as PsrUri;
use Stringable;

/** @psalm-api */
abstract class AbstractFactory implements Factory
{
    public readonly PsrRequestFactory $requestFactory;
    public readonly PsrResponseFactory $responseFactory;
    public readonly PsrServerRequestFactory $serverRequestFactory;
    public readonly PsrStreamFactory $streamFactory;
    public readonly PsrUploadedFileFactory $uploadedFileFactory;
    public readonly PsrUriFactory $uriFactory;

    abstract public function serverRequest(): PsrServerRequest;

    public function request(string $method, PsrUri|string $uri): PsrRequest
    {
        return $this->requestFactory->createRequest($method, $uri);
    }

    public function response(int $code = 200, string $reasonPhrase = ''): PsrResponse
    {
        return $this->responseFactory->createResponse($code, $reasonPhrase);
    }

    public function stream(mixed $content = ''): PsrStream
    {
        if (is_string($content) || $content instanceof Stringable) {
            return $this->streamFactory->createStream((string)$content);
        }

        if (is_resource($content)) {
            return $this->streamFactory->createStreamFromResource($content);
        }

        throw new RuntimeException('Only strings, Stringable or resources are allowed');
    }

    public function streamFromFile(string $filename, string $mode = 'r'): PsrStream
    {
        return $this->streamFactory->createStreamFromFile($filename, $mode);
    }

    public function uploadedFile(
        PsrStream $stream,
        int $size = null,
        int $error = \UPLOAD_ERR_OK,
        string $clientFilename = null,
        string $clientMediaType = null
    ): PsrUploadedFile {
        return $this->uploadedFileFactory->createUploadedFile($stream, $size, $error, $clientFilename, $clientMediaType);
    }

    public function uri(string $uri = ''): PsrUri
    {
        return $this->uriFactory->createUri($uri);
    }

    protected function setResponseFactory(PsrResponseFactory $factory): void
    {
        $this->responseFactory = $factory;
    }

    protected function setRequestFactory(PsrRequestFactory $factory): void
    {
        $this->requestFactory = $factory;
    }

    protected function setStreamFactory(PsrStreamFactory $factory): void
    {
        $this->streamFactory = $factory;
    }

    protected function setServerRequestFactory(PsrServerRequestFactory $factory): void
    {
        $this->serverRequestFactory = $factory;
    }

    protected function setUploadedFileFactory(PsrUploadedFileFactory $factory): void
    {
        $this->uploadedFileFactory = $factory;
    }

    protected function setUriFactory(PsrUriFactory $factory): void
    {
        $this->uriFactory = $factory;
    }
}
