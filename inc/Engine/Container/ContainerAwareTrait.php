<?php

namespace WP_Rocket\Engine\Container;

trait ContainerAwareTrait
{
    /**
     * @var \WP_Rocket\Engine\Container\ContainerInterface
     */
    protected $container;

    /**
     * Set a container.
     *
     * @param  \WP_Rocket\Engine\Container\ContainerInterface $container
     * @return $this
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Get the container.
     *
     * @return \WP_Rocket\Engine\Container\ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}
