<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\WPGeotargeting;

use WP_Rocket\ThirdParty\Plugins\WPGeotargeting;


use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
use Brain\Monkey\Filters;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\WPGeotargeting::activate_geotargetingwp
 */
class Test_activateGeotargetingwp extends TestCase {

    /**
     * @var WPGeotargeting
     */
    protected $wpgeotargeting;

    public function set_up() {
        parent::set_up();

        $this->wpgeotargeting = new WPGeotargeting();
    }

    public function testShouldDoAsExpected()
    {
		Filters\expectAdded('rocket_htaccess_mod_rewrite')->with([$this->wpgeotargeting, 'return_false'], 72);
		Filters\expectAdded('rocket_cache_dynamic_cookies')->with([$this->wpgeotargeting, 'add_geot_cookies']);
		Filters\expectAdded('rocket_cache_mandatory_cookies')->with([$this->wpgeotargeting, 'add_geot_cookies']);

		Functions\expect('flush_rocket_htaccess');
		Functions\expect('rocket_generate_config_file');

		$this->wpgeotargeting->activate_geotargetingwp();
    }
}
