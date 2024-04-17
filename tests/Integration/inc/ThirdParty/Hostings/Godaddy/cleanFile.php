<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Godaddy;

use Brain\Monkey\Filters;

class Test_cleanFile extends GodaddyTestCase
{
    public function testShouldPurgeFile()
    {
        Filters\expectApplied('pre_http_request')->andReturn('response');
        do_action('before_rocket_clean_file', home_url());
    }
}
