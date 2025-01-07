<?php

declare(strict_types=1);

namespace WP_Rocket\Dependencies\League\Container\Inflector;

use IteratorAggregate;
use WP_Rocket\Dependencies\League\Container\ContainerAwareInterface;

interface InflectorAggregateInterface extends ContainerAwareInterface, IteratorAggregate
{
    public function add(string $type, callable $callback = null): Inflector;
    public function inflect(object $object);
}
