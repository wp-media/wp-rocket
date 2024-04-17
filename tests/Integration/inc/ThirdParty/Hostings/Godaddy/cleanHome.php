<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Godaddy;

use Brain\Monkey\Filters;

class Test_cleanHome extends GodaddyTestCase
{
    public function testShouldPurgeHome()
    {
        Filters\expectApplied('pre_http_request')->andReturn('response');
        do_action('before_rocket_clean_home', 'wp-rocket/cache', '');
    }
}
