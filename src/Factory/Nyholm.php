<?php

declare(strict_types=1);

namespace Conia\Route\Factory;

use Conia\Route\Exception\RuntimeException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/** @psalm-api */
class Nyholm extends AbstractFactory
{
    protected Psr17Factory $factory;

    public function __construct()
    {
        try {
            $factory =  $this->factory = new Psr17Factory();
            $this->setResponseFactory($factory);
            $this->setStreamFactory($factory);
            $this->setRequestFactory($factory);
            $this->setServerRequestFactory($factory);
            $this->setUploadedFileFactory($factory);
            $this->setUriFactory($factory);
            // @codeCoverageIgnoreStart
        } catch (Throwable) {
            throw new RuntimeException('Install laminas/laminas-diactoros');
            // @codeCoverageIgnoreEnd
        }
    }

    public function serverRequest(): ServerRequestInterface
    {
        $creator = new ServerRequestCreator(
            $this->factory, // ServerRequestFactory
            $this->factory, // UriFactory
            $this->factory, // UploadedFileFactory
            $this->factory  // StreamFactory
        );

        return $creator->fromGlobals();
    }
}
