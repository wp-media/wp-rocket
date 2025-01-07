<?php

declare(strict_types=1);

namespace WP_Rocket\Dependencies\League\Container\Argument\Literal;

use WP_Rocket\Dependencies\League\Container\Argument\LiteralArgument;

class ObjectArgument extends LiteralArgument
{
    public function __construct(object $value)
    {
        parent::__construct($value, LiteralArgument::TYPE_OBJECT);
    }
}
