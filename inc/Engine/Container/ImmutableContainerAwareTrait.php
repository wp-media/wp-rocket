<?php

namespace WP_Rocket\Engine\Container;

use Psr\Container\ContainerInterface as InteropContainerInterface;

trait ImmutableContainerAwareTrait
{
    /**
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * Set a container.
     *
     * @param  \Psr\Container\ContainerInterface $container
     * @return $this
     */
    public function setContainer(InteropContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Get the container.
     *
     * @return \WP_Rocket\Engine\Container\ImmutableContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}
