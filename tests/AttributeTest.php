<?php

declare(strict_types=1);

namespace Conia\Route\Tests;

use Conia\Route\Renderer\Render;

class AttributeTest extends TestCase
{
    public function testRenderJsonString(): void
    {
        $render = new Render('json', a: 1, b: 2);

        $this->assertEquals($render->type, 'json');
        $this->assertEquals($render->args, ['a' => 1, 'b' => 2]);
    }
}
