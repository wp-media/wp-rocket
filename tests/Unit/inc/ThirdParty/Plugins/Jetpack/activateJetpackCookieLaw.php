<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Jetpack;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\Jetpack;
use Brain\Monkey\Functions;
use Brain\Monkey\Filters;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Jetpack::activate_jetpack_cookie_law
 * @group Jetpack
 * @group ThirdParty
 */
class Test_ActivateJetpackCookieLaw extends TestCase
{
	protected $option;
	protected $subscriber;

	protected function setUp(): void
	{
		parent::setUp();
		$this->option = Mockery::mock(Options_Data::class);
		$this->subscriber = new Jetpack($this->option);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config) {
		$this->option->expects()->get('rocket_jetpack_eu_cookie_widget')->andReturn($config['is_eu_widget_present']);
		Functions\expect('is_active_widget')->with(false, false, 'eu_cookie_law_widget')->andReturn($config['is_active']);

		$this->subscriber->activate_jetpack_cookie_law();
	}

	protected function configureAddFilters($config) {
		if(!$config['is_active'] || ! empty($config['is_eu_widget_present'])) {
			return;
		}
		Filters\expectAdded('rocket_htaccess_mod_rewrite')->with('__return_false', 76);
		Filters\expectAdded('rocket_cache_mandatory_cookies')->with(Jetpack::class, 'add_jetpack_cookie_law_mandatory_cookie');
		Functions\expect('flush_rocket_htaccess');
		Functions\expect('rocket_generate_config_file');
		Functions\expect('update_option')->with('rocket_jetpack_eu_cookie_widget', 1, true);
	}
}
