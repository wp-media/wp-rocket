<?php

namespace WP_Rocket\Dependencies\League\Container\Exception;

use WP_Rocket\Dependencies\Psr\Container\NotFoundExceptionInterface;
use InvalidArgumentException;

class NotFoundException extends InvalidArgumentException implements NotFoundExceptionInterface
{
}
