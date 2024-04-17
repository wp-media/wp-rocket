<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\DynamicLists\Subscriber;

use WPMedia\PHPUnit\Integration\RESTfulTestCase;

class Test_RegisterRestRoute extends RESTfulTestCase
{
    public function testShouldRegisterRoute()
    {
        $this->assertArrayHasKey('/wp-rocket/v1/dynamic_lists/update', $this->server->get_routes());
    }
}
