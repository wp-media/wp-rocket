<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\SpinUpWP;

use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;

class Test_PurgeSite extends TestCase
{
    public function testShouldCallSpinUpPurgeSite()
    {
        Functions\expect('spinupwp_purge_site')->once();
        do_action('rocket_after_clean_domain');
    }
}
