<?php

declare(strict_types=1);

namespace WP_Rocket\Dependencies\League\Container\Argument;

interface ArgumentInterface
{
    /**
     * @return mixed
     */
    public function getValue();
}
