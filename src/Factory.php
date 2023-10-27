<?php

declare(strict_types=1);

namespace Conia\Route;

use Psr\Http\Message\RequestInterface as PsrRequest;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use Psr\Http\Message\ServerRequestInterface as PsrServerRequest;
use Psr\Http\Message\StreamInterface as PsrStream;
use Psr\Http\Message\UploadedFileInterface as PsrUploadedFile;
use Psr\Http\Message\UriInterface as PsrUri;

/** @psalm-api */
interface Factory
{
    public function serverRequest(): PsrServerRequest;

    public function request(string $method, PsrUri|string $uri): PsrRequest;

    public function response(int $code = 200, string $reasonPhrase = ''): PsrResponse;

    public function stream(mixed $content = ''): PsrStream;

    public function streamFromFile(string $filename, string $mode = 'r'): PsrStream;

    public function uploadedFile(
        PsrStream $stream,
        int $size = null,
        int $error = \UPLOAD_ERR_OK,
        string $clientFilename = null,
        string $clientMediaType = null
    ): PsrUploadedFile;

    public function uri(string $uri = ''): PsrUri;
}
