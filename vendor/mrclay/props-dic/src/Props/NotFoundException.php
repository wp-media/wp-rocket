<?php

namespace Props;

use Interop\Container\Exception\NotFoundException as NotFound;

class NotFoundException extends \Exception implements NotFound
{
}
