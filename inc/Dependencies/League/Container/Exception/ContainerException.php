<?php

declare(strict_types=1);

namespace WP_Rocket\Dependencies\League\Container\Exception;

use WP_Rocket\Dependencies\Psr\Container\ContainerExceptionInterface;
use RuntimeException;

class ContainerException extends RuntimeException implements ContainerExceptionInterface
{
}
