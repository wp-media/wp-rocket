<?php

namespace WP_Rocket\Tests\Integration\inc;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Integration\TestCase;

class Test_RocketActivation extends TestCase
{
    public function testShouldNotLoadHostingFilesWhenNotInActivation()
    {
        Functions\expect('rocket_get_constant')->with('O2SWITCH_VARNISH_PURGE_KEY')->andReturn('varnish_key');
        $included_files = get_included_files();
        $this->assertNotContains(WP_ROCKET_3RD_PARTY_PATH . 'hosting/wpengine.php', $included_files);
        $this->assertNotContains(WP_ROCKET_3RD_PARTY_PATH . 'hosting/o2switch.php', $included_files);
    }
}
