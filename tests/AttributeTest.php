<?php

declare(strict_types=1);

namespace Conia\Route\Tests;

use Conia\Route\Renderer\Render;
use Conia\Route\Renderer\Renderer;
use Conia\Route\Tests\Fixtures\TestRenderer;
use Conia\Route\Tests\Fixtures\TestRendererArgsOptions;

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
        $render = new Render('json');
        $response = $render->response($this->registry(), ['a' => 1, 'b' => 2]);

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

    public function testRenderTestRendererWithArgsAndOptions(): void
    {
        $registry = $this->registry();
        $registry
            ->tag(Renderer::class)
            ->add('test', TestRendererArgsOptions::class)
            ->args(option1: 13, option2: 'Option');
        $render = new Render('test', contentType: 'application/xhtml+xml');
        $response = $render->response($registry, ['a' => 1, 'b' => 2]);

        $this->assertEquals('{"a":1,"b":2,"contentType":"application/xhtml+xml","option1":13,"option2":"Option"}', $this->fullTrim((string)$response->getBody()));
    }

    public function testRenderTestRendererWithOptionsClosure(): void
    {
        $registry = $this->registry();
        $registry
            ->tag(Renderer::class)
            ->add('test', TestRendererArgsOptions::class)
            ->args(fn () => ['option1' => 13, 'option2' => 'Option']);
        $render = new Render('test');
        $response = $render->response($registry, ['a' => 1, 'b' => 2]);

        $this->assertEquals('{"a":1,"b":2,"option1":13,"option2":"Option"}', $this->fullTrim((string)$response->getBody()));
    }
}
