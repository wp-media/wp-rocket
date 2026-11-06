<?php

declare(strict_types=1);

namespace WP_Rocket\Dependencies\League\Container\Argument;

interface ResolvableArgumentInterface extends ArgumentInterface
{
    public function getValue(): string;
}
