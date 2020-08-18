<?php

namespace WP_Rocket\Engine\Container;

interface ContainerAwareInterface
{
    /**
     * Set a container
     *
     * @param \WP_Rocket\Engine\Container\ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container);

    /**
     * Get the container
     *
     * @return \WP_Rocket\Engine\Container\ContainerInterface
     */
    public function getContainer();
}
