<?php

declare(strict_types=1);

namespace Conia\Route;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as ServerRequest;
use Psr\Http\Message\StreamInterface as Stream;
use Psr\Http\Message\UploadedFileInterface as UploadedFile;
use Psr\Http\Message\UriInterface as Uri;

/** @psalm-api */
interface Factory
{
    public function serverRequest(): ServerRequest;

    public function request(string $method, Uri|string $uri): Request;

    public function response(int $code = 200, string $reasonPhrase = '', string|Stream $body = null): Response;

    public function stream(mixed $content = ''): Stream;

    public function streamFromFile(string $filename, string $mode = 'r'): Stream;

    public function uploadedFile(
        Stream $stream,
        int $size = null,
        int $error = \UPLOAD_ERR_OK,
        string $clientFilename = null,
        string $clientMediaType = null
    ): UploadedFile;

    public function uri(string $uri = ''): Uri;
}
