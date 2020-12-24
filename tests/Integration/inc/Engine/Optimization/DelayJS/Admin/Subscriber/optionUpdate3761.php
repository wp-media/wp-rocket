<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\DelayJS\Admin\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DelayJS\Admin\Subscriber::option_update_3_7_4
 *
 * @group  DelayJS
 */
class Test_optionUpdate3761 extends TestCase {
	public function setUp() {
		parent::setUp();

		$this->unregisterAllCallbacksExcept( 'wp_rocket_upgrade', 'option_update_3_7_6_1', 13 );
	}

	public function tearDown() {
		parent::tearDown();

		$this->restoreWpFilter( 'wp_rocket_upgrade' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$options = get_option( 'wp_rocket_settings' );
		$options['delay_js_scripts'] = $config['initial_list'];

		update_option( 'wp_rocket_settings', $options );

		do_action( 'wp_rocket_upgrade', '', $config['old_version'] );

		$options = get_option( 'wp_rocket_settings' );

		$this->assertSame( $expected, $options['delay_js_scripts']);
	}
}
