<?php

declare(strict_types=1);

if (!function_exists('testJsonRendererIterator')) {
    function testJsonRendererIterator()
    {
        $arr = [13, 31, 73];

        foreach ($arr as $a) {
            yield $a;
        }
    }
}
