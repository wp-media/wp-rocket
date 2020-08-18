<?php

namespace WP_Rocket\Engine\Container\Definition;

use WP_Rocket\Engine\Container\ImmutableContainerAwareInterface;

interface DefinitionFactoryInterface extends ImmutableContainerAwareInterface
{
    /**
     * Return a definition based on type of concrete.
     *
     * @param  string $alias
     * @param  mixed  $concrete
     * @return mixed
     */
    public function getDefinition($alias, $concrete);
}
