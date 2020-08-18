<?php

namespace WP_Rocket\Engine\Container;

interface ContainerInterface extends ImmutableContainerInterface
{
    /**
     * Add an item to the container.
     *
     * @param  string     $alias
     * @param  mixed|null $concrete
     * @param  boolean    $share
     * @return \WP_Rocket\Engine\Container\Definition\DefinitionInterface
     */
    public function add($alias, $concrete = null, $share = false);

    /**
     * Convenience method to add an item to the container as a shared item.
     *
     * @param  string     $alias
     * @param  mixed|null $concrete
     * @return \WP_Rocket\Engine\Container\Definition\DefinitionInterface
     */
    public function share($alias, $concrete = null);

    /**
     * Add a service provider to the container.
     *
     * @param  string|\WP_Rocket\Engine\Container\ServiceProvider\ServiceProviderInterface $provider
     * @return void
     */
    public function addServiceProvider($provider);

    /**
     * Returns a definition of an item to be extended.
     *
     * @param  string $alias
     * @return \WP_Rocket\Engine\Container\Definition\DefinitionInterface
     */
    public function extend($alias);

    /**
     * Allows for manipulation of specific types on resolution.
     *
     * @param  string        $type
     * @param  callable|null $callback
     * @return \WP_Rocket\Engine\Container\Inflector\Inflector|void
     */
    public function inflector($type, callable $callback = null);

    /**
     * Invoke a callable via the container.
     *
     * @param  callable $callable
     * @param  array    $args
     * @return mixed
     */
    public function call(callable $callable, array $args = []);
}
