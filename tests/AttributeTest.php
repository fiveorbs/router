<?php

declare(strict_types=1);

namespace Conia\Route\Tests;

use Conia\Route\Renderer\Render;
use Conia\Route\Renderer\Renderer;
use Conia\Route\Tests\Fixtures\TestRenderer;

class AttributeTest extends TestCase
{
    public function testRenderJsonString(): void
    {
        $render = new Render('json');
        $result = $render->render($this->registry(), ['a' => 1, 'b' => 2]);

        $this->assertEquals('{"a":1,"b":2}', $result);
    }

    public function testRenderJsonResponse(): void
    {
        $registry = $this->registry();
        $registry->tag(Renderer::class)->add('test', TestRenderer::class);
        $render = new Render('json');
        $response = $render->response($registry, ['a' => 1, 'b' => 2]);

        $this->assertEquals('{"a":1,"b":2}', (string)$response->getBody());
    }

    public function testRenderTestRenderer(): void
    {
        $registry = $this->registry();
        $registry->tag(Renderer::class)->add('test', TestRenderer::class);
        $render = new Render('test', contentType: 'application/xhtml+xml');
        $response = $render->response($registry, ['a' => 1, 'b' => 2]);

        $this->assertEquals('Array( [a] => 1 [b] => 2)', $this->fullTrim((string)$response->getBody()));
    }
}
