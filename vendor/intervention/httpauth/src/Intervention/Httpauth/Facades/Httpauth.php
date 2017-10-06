<?php

namespace Intervention\Httpauth\Facades;

use Illuminate\Support\Facades\Facade;

class Httpauth extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'httpauth';
    }
}
