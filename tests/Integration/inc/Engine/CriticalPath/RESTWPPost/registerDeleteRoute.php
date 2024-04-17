<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\RESTWPPost;

use WP_Rocket\Tests\Integration\RESTVfsTestCase;

class Test_RegisterDeleteRouter extends RESTVfsTestCase
{
    protected $path_to_test_data = '/inc/Engine/CriticalPath/RESTWPPost/delete.php';
    /**
     * Test should register the disable route with the WP REST API.
     */
    public function testShouldRegisterRoute()
    {
        $routes = $this->server->get_routes();
        $this->assertArrayHasKey('/wp-rocket/v1/cpcss/post/(?P<id>[\\d]+)', $routes);
    }
}
