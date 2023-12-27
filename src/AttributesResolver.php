<?php

declare(strict_types=1);

namespace Conia\Route;

use Closure;
use ReflectionAttribute;
use ReflectionFunction;
use ReflectionObject;
use Conia\Wire\CallableResolver;
use Conia\Wire\Creator;
use Psr\Container\ContainerInterface as Container;

class AttributesResolver
{
    protected readonly array $attributes;

    public function __construct(
        Closure $closure,
        protected readonly ?Container $container,
    ) {

        $reflector = new ReflectionFunction($closure);

        $this->attributes = array_map(function ($attribute) {
            return $this->newAttributeInstance($attribute);
        }, $reflector->getAttributes());
    }

    public function get(?string $filter = null): array
    {
        if ($filter) {
            return array_values(
                array_filter($this->attributes, function ($attribute) use ($filter) {
                    return $attribute instanceof $filter;
                })
            );
        }

        return $this->attributes;
    }

    protected function newAttributeInstance(ReflectionAttribute $attribute): object
    {
        $instance = $attribute->newInstance();
        $callAttrs = (new ReflectionObject($instance))->getAttributes(Call::class);

        if (count($callAttrs) > 0 ) {
            $resolver = new CallableResolver(new Creator($this->container));

            // See if the attribute itself has one or more Call attributes. If so,
            // resolve/autowire the arguments of the method it states and call it.
            foreach ($callAttrs as $callAttr) {
                $callAttr = $callAttr->newInstance();
                $methodToResolve = $callAttr->method;

                /** @psalm-var callable */
                $callable = [$instance, $methodToResolve];
                $args = $resolver->resolve($callable, $callAttr->args);
                $callable(...$args);
            }
        }

        return $instance;
    }
}
