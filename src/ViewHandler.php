<?php

declare(strict_types=1);

namespace Conia\Route;

use Conia\Registry\Registry;
use Conia\Route\Route;
use Psr\Http\Message\ResponseInterface as PsrResponse;

class ViewHandler
{
    public function __construct(
        protected readonly View $view,
        protected readonly Registry $registry,
        protected readonly Route $route,
    ) {
    }

    public function __invoke(): PsrResponse
    {
        return $this->view->respond(
            $this->route,
            $this->registry,
        );
    }
}
