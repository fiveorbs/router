<?php

declare(strict_types=1);

namespace Conia\Route\Tests;

use Conia\Route\Renderer\HtmlRenderer;
use Conia\Route\Renderer\JsonRenderer;
use Conia\Route\Renderer\TextRenderer;

class RendererTest extends TestCase
{
    public function testJsonRendererRender(): void
    {
        $renderer = new JsonRenderer($this->responseFactory(), []);

        $this->assertEquals('{"album":"Spiritual Healing","released":1990}', $renderer->render([ 'album' => 'Spiritual Healing', 'released' => 1990, ]));
    }

    public function testJsonRendererRenderIterator(): void
    {
        $renderer = new JsonRenderer($this->responseFactory(), []);

        $this->assertEquals('[13,31,73]', $renderer->render(testJsonRendererIterator()));
    }

    public function testJsonRendererRenderWithFlags(): void
    {
        $renderer = new JsonRenderer($this->responseFactory(), []);

        $this->assertEquals('{"path":"album/leprosy"}', $renderer->render([ 'path' => 'album/leprosy', ]));
        $this->assertEquals('{"path":"album\/leprosy"}', $renderer->render([ 'path' => 'album/leprosy', ], JSON_THROW_ON_ERROR));
    }

    public function testJsonRendererResponse(): void
    {
        $renderer = new JsonRenderer($this->responseFactory(), []);

        $this->assertEquals('{"album":"Spiritual Healing","released":1990}', (string)$renderer->response([ 'album' => 'Spiritual Healing', 'released' => 1990, ])->getBody());

        $renderer = new JsonRenderer($this->responseFactory(), []);

        $response = $renderer->response(testJsonRendererIterator());
        $this->assertEquals('[13,31,73]', (string)$response->getBody());

        $hasContentType = false;

        foreach ($response->getHeaders() as $key => $value) {
            if ($key === 'Content-Type' && $value[0] === 'application/json') {
                $hasContentType = true;
            }
        }

        $this->assertEquals(true, $hasContentType);
    }

    public function testHtmlRenderer(): void
    {
        $renderer = new HtmlRenderer($this->responseFactory(), []);
        $response = $renderer->response('<h1>Symbolic</h1>');

        $hasContentType = false;

        foreach ($response->getHeaders() as $key => $value) {
            if ($key === 'Content-Type' && $value[0] === 'text/html') {
                $hasContentType = true;
            }
        }

        $this->assertEquals(true, $hasContentType);
        $this->assertEquals('<h1>Symbolic</h1>', (string)$response->getBody());
    }

    public function testTextRenderer(): void
    {
        $renderer = new TextRenderer($this->responseFactory(), []);
        $response = $renderer->response('Symbolic');

        $hasContentType = false;

        foreach ($response->getHeaders() as $key => $value) {
            if ($key === 'Content-Type' && $value[0] === 'text/plain') {
                $hasContentType = true;
            }
        }

        $this->assertEquals(true, $hasContentType);
        $this->assertEquals('Symbolic', (string)$response->getBody());
    }
}
