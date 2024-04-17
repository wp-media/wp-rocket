<?php

namespace WP_Rocket\Tests\Integration\Inc\Engine\Plugin\RenewalNotice;

use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\Plugin\RenewalNotice;
use WP_Rocket\Tests\Integration\TestCase;

class TestRenewalNotice extends TestCase
{
    /**
     * @dataProvider configTestData
     */
    public function testShouldDoExpected($config, $expected)
    {
        $this->rocket_version = $config['current_version'];
        $user = new User($config['user']);
        $renewal = new RenewalNotice($user, WP_ROCKET_PLUGIN_ROOT . '/views/plugins/');
        $renewal->renewal_notice($config['version']);
        $this->expectOutputContains($expected['output']);
    }
}
