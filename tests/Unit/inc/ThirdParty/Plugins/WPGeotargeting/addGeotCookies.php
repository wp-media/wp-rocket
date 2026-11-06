<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\WPGeotargeting;

use WP_Rocket\ThirdParty\Plugins\WPGeotargeting;


use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Filters;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\WPGeotargeting::add_geot_cookies
 */
class Test_addGeotCookies extends TestCase {

    /**
     * @var WPGeotargeting
     */
    protected $wpgeotargeting;

    public function set_up() {
        parent::set_up();

        $this->wpgeotargeting = new WPGeotargeting();
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
		Filters\expectApplied('rocket_geotargetingwp_enabled_cookies')->with([ 'country' ])->andReturn($config['enabled_cookies']);
        $this->assertSame($expected, $this->wpgeotargeting->add_geot_cookies($config['cookies']));
    }
}
