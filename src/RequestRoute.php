<?php

declare(strict_types=1);

namespace Conia\Route;

use Psr\Http\Message\RequestInterface as Request;

const LEFT_BRACE = '§§§€§§§';
const RIGHT_BRACE = '§§§£§§§';

readonly class RequestRoute
{
    public function __construct(public Route $route, public Request $request) {
    }
}
