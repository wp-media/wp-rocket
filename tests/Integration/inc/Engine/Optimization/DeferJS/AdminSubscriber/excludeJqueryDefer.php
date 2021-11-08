<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\DeferJS\AdminSubscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DeferJS\AdminSubscriber::exclude_jquery_defer
 *
 * @group  DeferJS
 * @group  AdminOnly
 */
class Test_ExcludeJqueryDefer extends TestCase {
	public function setUp() : void {
		parent::setUp();

		$this->unregisterAllCallbacksExcept( 'wp_rocket_upgrade', 'exclude_jquery_defer', 14 );
	}

	public function tearDown() {
		parent::tearDown();

		$this->restoreWpFilter( 'wp_rocket_upgrade' );

		delete_option( 'wp_rocket_settings' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$options = get_option( 'wp_rocket_settings' );

		$options['defer_all_js_safe'] = $config['options']['defer_all_js_safe'];

		update_option( 'wp_rocket_settings', $options );

		do_action( 'wp_rocket_upgrade', '', $config['old_version'] );

		$options = get_option( 'wp_rocket_settings' );

		$this->assertSame(
			$expected,
			$options
		);
	}
}
