<?php

declare(strict_types=1);

namespace Conia\Route\Tests;

use Conia\Route\Http\Emitter;
use Conia\Route\Response;
use Conia\Route\Tests\Setup\C;

class EmitterTest extends TestCase
{
    public function testSapiEmitter(): void
    {
        $factory = Response::fromFactory($this->factory());
        $response = $factory->json([1, 2, 3]);

        $emitter = new Emitter();
        ob_start();
        $emitter->emit($response->psr());
        $output = ob_get_contents();
        ob_end_clean();

        expect($output)->toBe('[1,2,3]');
    }

    public function testSapiStreamEmitter(): void
    {
        $file = C::root() . '/public/static/pixel.gif';
        $factory = Response::fromFactory($this->factory());
        $response = $factory->download($file);

        $emitter = new Emitter();
        ob_start();
        $emitter->emit($response->psr());
        $output = ob_get_contents();
        ob_end_clean();

        expect(str_starts_with($output, 'GIF87a'))->toBe(true);
    }
}
