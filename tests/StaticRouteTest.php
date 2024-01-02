<?php

declare(strict_types=1);

namespace Conia\Route\Tests;

use Conia\Route\Exception\RuntimeException;
use Conia\Route\Router;

class StaticRouteTest extends TestCase
{
    public function testStaticRoutesUnnamed(): void
    {
        $router = new Router();
        $router->addStatic('/static', $this->root . '/public/static');

        $this->assertSame('/static/test.json', $router->staticUrl('/static', 'test.json'));
        $this->assertMatchesRegularExpression('/\?v=[a-f0-9]{8}$/', $router->staticUrl('/static', 'test.json', true));
        $this->assertMatchesRegularExpression('/\?exists=true&v=[a-f0-9]{8}$/', $router->staticUrl('/static', 'test.json?exists=true', true));
        $this->assertMatchesRegularExpression(
            '/https:\/\/conia.local\/static\/test.json\?v=[a-f0-9]{8}$/',
            $router->staticUrl(
                '/static',
                'test.json',
                host: 'https://conia.local/',
                bust: true,
            )
        );
    }

    public function testStaticRoutesNamed(): void
    {
        $router = new Router();
        $router->addStatic('/static', $this->root . '/public/static', 'staticroute');

        $this->assertSame('/static/test.json', $router->staticUrl('staticroute', 'test.json'));
    }

    public function testStaticRoutesToNonexistentDirectory(): void
    {
        $this->throws(RuntimeException::class, 'does not exist');

        (new Router())->addStatic('/static', $this->root . '/fantasy/dir');
    }

    public function testNonExistingFilesNoCachebuster(): void
    {
        $router = new Router();
        $router->addStatic('/static', $this->root . '/public/static');

        // Non existing files should not have a cachebuster attached
        $this->assertMatchesRegularExpression('/https:\/\/conia.local\/static\/does-not-exist.json$/', $router->staticUrl(
            '/static',
            'does-not-exist.json',
            host: 'https://conia.local/',
            bust: true,
        ));
    }

    public function testStaticRouteDuplicateNamed(): void
    {
        $this->throws(RuntimeException::class, 'Duplicate static route: static');

        $router = new Router();
        $router->addStatic('/static', $this->root . '/public/static', 'static');
        $router->addStatic('/anotherstatic', $this->root . '/public/static', 'static');
    }

    public function testStaticRouteDuplicateUnnamed(): void
    {
        $this->throws(RuntimeException::class, 'Duplicate static route: /static');

        $router = new Router();
        $router->addStatic('/static', $this->root . '/public/static');
        $router->addStatic('/static', $this->root . '/public/static');
    }
}
