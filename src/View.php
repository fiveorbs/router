<?php

declare(strict_types=1);

namespace Conia\Route;

use Closure;
use Conia\Route\Exception\RuntimeException;
use Conia\Route\Renderer\Config as RendererConfig;
use Conia\Route\Route;
use Conia\Wire\Creator;
use Psr\Container\ContainerExceptionInterface as ContainerException;
use Psr\Container\ContainerInterface as Container;
use Psr\Container\NotFoundExceptionInterface as NotFoundException;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use ReflectionClass;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionObject;
use ReflectionParameter;
use Throwable;

class View
{
    protected Creator $creator;
    protected ?AttributesResolver $attributes = null;
    protected ?Closure $closure = null;
    protected ?ReflectionMethod $controllerConstructor = null;
    protected Closure|array $view;

    public function __construct(
        protected readonly Route $route,
        protected readonly ?Container $container,
    ) {
        $this->creator = new Creator($container);
        $this->view = $this->prepareView($route->view());
    }

    public function execute(Request $request): mixed
    {
        $closure = $this->getClosure($request);

        return ($closure)(...$this->getArgs(
            self::getReflectionFunction($closure),
            $request,
        ));
    }

    protected static function getReflectionFunction(
        callable $callable
    ): ReflectionFunction|ReflectionMethod {
        if ($callable instanceof Closure) {
            return new ReflectionFunction($callable);
        }

        if (is_object($callable)) {
            return (new ReflectionObject($callable))->getMethod('__invoke');
        }

        /** @var Closure|non-falsy-string $callable */
        return new ReflectionFunction($callable);
    }

    /** @psalm-param $filter ?class-string */
    public function attributes(string $filter = null): array
    {
        if (!isset($this->attributes)) {
            if (is_callable($this->view)) {
                $this->attributes = new AttributesResolver([self::getReflectionFunction($this->view)], $this->container);
            } else {
                [$controller, $method] = $this->view;
                $reflectionClass = new ReflectionClass($controller);
                $this->attributes = new AttributesResolver([
                    $reflectionClass,
                    $reflectionClass->getMethod($method),
                ], $this->container);
            }

        }

        return $this->attributes->get($filter);
    }

    protected function prepareView(callable|string|array $view): Closure|array
    {
        if (is_callable($view)) {
            /** @var callable $view -- Psalm complains even though we use is_callable() */
            return Closure::fromCallable($view);
        }

        if (is_array($view)) {
            [$controllerName, $method] = $view;
            assert(is_string($controllerName));
            assert(is_string($method));
        } else {
            if (!str_contains($view, '::')) {
                $view .= '::__invoke';
            }

            [$controllerName, $method] = explode('::', $view);
        }

        if (class_exists($controllerName)) {
            return [$controllerName, $method];
        }

        throw new RuntimeException("Controller not found {$controllerName}");
    }

    protected function getClosure(Request $request): Closure
    {
        if ($this->view instanceof Closure) {
            return $this->view;
        }

        [$controllerName, $method] = $this->view;
        $rc = new ReflectionClass($controllerName);
        $constructor = $rc->getConstructor();
        $args = $constructor ? $this->getArgs($constructor, $request) : [];
        $controller = $rc->newInstance(...$args);

        if (method_exists($controller, $method)) {
            return Closure::fromCallable([$controller, $method]);
        }
        $view = $controllerName . '::' . $method;

        throw new RuntimeException("View method not found {$view}");
    }

    /**
     * Determines the arguments passed to the view and/or controller constructor.
     *
     * - If a view parameter implements Request, the request will be passed.
     * - If names of the view parameters match names of the route arguments
     *   it will try to convert the argument to the parameter type and add it to
     *   the returned args list.
     * - If the parameter is typed, try to resolve it via container or
     *   autowiring.
     * - Otherwise fail.
     *
     * @psalm-suppress MixedAssignment -- $args values are mixed
     */
    protected function getArgs(ReflectionFunctionAbstract $rf, Request $request): array
    {
        /** @var array<string, mixed> */
        $args = [];
        $params = $rf->getParameters();
        $errMsg = 'View parameters cannot be resolved. Details: ';

        foreach ($params as $param) {
            $name = $param->getName();
            $routeArgs = $this->route->args();

            try {
                $args[$name] = match ((string)$param->getType()) {
                    'int' => is_numeric($routeArgs[$name]) ?
                        (int)$routeArgs[$name] :
                        throw new RuntimeException($errMsg . "Cannot cast '{$name}' to int"),
                    'float' => is_numeric($routeArgs[$name]) ?
                        (float)$routeArgs[$name] :
                        throw new RuntimeException($errMsg . "Cannot cast '{$name}' to float"),
                    'string' => $routeArgs[$name],
                    default => $this->resolveParam($param, $request),
                };
            } catch (Throwable $e) {
                // Check if the view parameter has a default value
                if (!array_key_exists($name, $routeArgs) && $param->isDefaultValueAvailable()) {
                    $args[$name] = $param->getDefaultValue();

                    continue;
                }

                throw new ($e::class)($errMsg . $e->getMessage(), $e->getCode(), $e);
            }
        }

        assert(count($params) === count($args));

        return $args;
    }

    protected function resolveParam(ReflectionParameter $param, Request $request): mixed
    {
        $type = $param->getType();

        if ($type instanceof Request) {
            return $request;
        }

        if ($type instanceof Route) {
            return $this->route;
        }

        if ($type instanceof ReflectionNamedType) {
            try {
                return $this->creator->create(ltrim($type->getName(), '?'));
            } catch (NotFoundException | ContainerException  $e) {
                if ($param->isDefaultValueAvailable()) {
                    return $param->getDefaultValue();
                }

                throw $e;
            }
        } else {
            if ($type) {
                throw new ContainerException(
                    "Autowiring does not support union or intersection types. Source: \n" .
                        $this->paramInfo($param)
                );
            }

            throw new ContainerException(
                "Autowired entities need to have typed constructor parameters. Source: \n" .
                    $this->paramInfo($param)
            );
        }
    }

    public function paramInfo(ReflectionParameter $param): string
    {
        $type = $param->getType();
        $rf = $param->getDeclaringFunction();
        $rc = null;

        if ($rf instanceof ReflectionMethod) {
            $rc = $rf->getDeclaringClass();
        }

        return ($rc ? $rc->getName() . '::' : '') .
            ($rf->getName() . '(..., ') .
            ($type ? (string)$type . ' ' : '') .
            '$' . $param->getName() . ', ...)';
    }

    public function middleware(): array
    {
        $middlewareAttributes = $this->attributes(Middleware::class);

        return array_merge(
            $this->route->getMiddleware(),
            $middlewareAttributes,
        );
    }

    public function renderer(): ?RendererConfig
    {
        return $this->route->renderer();
    }
}
