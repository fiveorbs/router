<?php

declare(strict_types=1);

namespace Conia\Route;

use Closure;
use Conia\Route\AddsMiddleware;
use Conia\Route\AddsRoutes;
use Conia\Route\Exception\RuntimeException;
use Conia\Route\RouteAdder;

/** @psalm-api */
class Group implements RouteAdder
{
    use AddsBeforeAfter;
    use AddsMiddleware;
    use AddsRoutes;

    /** @psalm-var list<Group> */
    protected array $subgroups = [];

    protected ?RouteAdder $routeAdder = null;
    protected ?string $controller = null;
    protected bool $created = false;

    public function __construct(
        protected string $patternPrefix,
        protected Closure $createClosure,
        protected string $namePrefix = '',
    ) {
    }

    public function controller(string $controller): static
    {
        $this->controller = $controller;

        return $this;
    }

    public function addRoute(Route $route): Route
    {
        $route->prefix($this->patternPrefix, $this->namePrefix);

        if ($this->controller) {
            $route->controller($this->controller);
        }

        $route->replaceMiddleware(array_merge($this->middleware, $route->getMiddleware()));
        $route->replaceBeforeHandlers(array_merge($this->beforeHandlers, $route->beforeHandlers()));
        $route->replaceAfterHandlers(array_merge($this->afterHandlers, $route->afterHandlers()));

        if ($this->routeAdder) {
            $this->routeAdder->addRoute($route);

            return $route;
        }

        throw new RuntimeException('RouteAdder not set');
    }

    public function addGroup(Group $group): void
    {
        $group->create($this);
    }

    public function group(
        string $patternPrefix,
        Closure $createClosure,
        string $namePrefix = '',
    ): Group {
        $group = new Group($patternPrefix, $createClosure, $namePrefix);
        $this->subgroups[] = $group;

        return $group;
    }

    public function create(RouteAdder $adder): void
    {
        if ($this->created) {
            return;
        }

        $this->created = true;
        $this->routeAdder = $adder;
        ($this->createClosure)($this);

        foreach ($this->subgroups as $subgroup) {
            $subgroup->create($this);
        }
    }
}
