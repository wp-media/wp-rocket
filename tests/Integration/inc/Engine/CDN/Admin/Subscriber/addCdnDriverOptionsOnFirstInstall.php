<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Subscriber;

/**
 * @covers \WP_Rocket\Engine\CDN\Subscriber::add_cdn_driver_options_on_first_install
 * @group  CDN
 * @group AdminOnly
 */
class Test_AddCdnDriverOptionsOnFirstInstall extends TestCase {

	private $hook_name = 'rocket_first_install_options';

	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( $this->hook_name, 'add_cdn_driver_options_on_first_install', 10 );
	}

	public function tear_down() {
		$this->restoreWpHook( $this->hook_name );
		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( array $config, array $expected ) {
		$options = wpm_apply_filters_typed( 'array', $this->hook_name, $config );

		$this->assertSame( $expected, $options );
	}
}
