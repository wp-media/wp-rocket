<?php

namespace WP_Rocket\Engine\Container;

use Interop\Container\ContainerInterface as InteropContainerInterface;

interface ImmutableContainerAwareInterface
{
    /**
     * Set a container
     *
     * @param \Interop\Container\ContainerInterface $container
     */
    public function setContainer(InteropContainerInterface $container);

    /**
     * Get the container
     *
     * @return \WP_Rocket\Engine\Container\ImmutableContainerInterface
     */
    public function getContainer();
}
