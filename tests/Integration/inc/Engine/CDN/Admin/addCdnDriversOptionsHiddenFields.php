<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Admin;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\Admin\Subscriber::add_cdn_drivers_options_hidden_fields
 * @group  CDN
 * @group  AdminOnly
 */
class Test_AddCdnDriversOptionsHiddenFields extends TestCase {

	private $hook_name = 'rocket_hidden_settings_fields';

	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( $this->hook_name, 'add_cdn_drivers_options_hidden_fields', 10 );
	}

	public function tear_down() {
		$this->restoreWpHook( $this->hook_name );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
		$hidden_fields = wpm_apply_filters_typed( 'array', $this->hook_name, $config );

		$this->assertSame( $expected, $hidden_fields );
	}
}
