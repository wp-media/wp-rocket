<?php

declare(strict_types=1);

namespace WP_Rocket\Dependencies\League\Container\Argument;

use WP_Rocket\Dependencies\League\Container\ContainerAwareInterface;
use ReflectionFunctionAbstract;

interface ArgumentResolverInterface extends ContainerAwareInterface
{
    public function resolveArguments(array $arguments): array;
    public function reflectArguments(ReflectionFunctionAbstract $method, array $args = []): array;
}
