<?php

declare(strict_types=1);

namespace WP_Rocket\Dependencies\League\Container\Argument\Literal;

use WP_Rocket\Dependencies\League\Container\Argument\LiteralArgument;

class FloatArgument extends LiteralArgument
{
    public function __construct(float $value)
    {
        parent::__construct($value, LiteralArgument::TYPE_FLOAT);
    }
}
