<?php

declare(strict_types=1);

namespace WP_Rocket\Dependencies\League\Container\Argument;

class ResolvableArgument implements ResolvableArgumentInterface
{
    protected $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
