<?php

namespace WP_Rocket\Engine\Container;

use Psr\Container\ContainerInterface as InteropContainerInterface;

interface ImmutableContainerAwareInterface
{
    /**
     * Set a container
     *
     * @param \Psr\Container\ContainerInterface $container
     */
    public function setContainer(InteropContainerInterface $container);

    /**
     * Get the container
     *
     * @return \WP_Rocket\Engine\Container\ImmutableContainerInterface
     */
    public function getContainer();
}
