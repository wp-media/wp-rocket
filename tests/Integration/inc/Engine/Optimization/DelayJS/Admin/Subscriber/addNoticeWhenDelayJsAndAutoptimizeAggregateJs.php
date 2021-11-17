<?php

declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\DelayJS\Admin\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DelayJS\Admin\Subscriber::add_notice_when_delayjs_and_autoptimize_aggreatejs
 *
 * @group DelayJS
 * @group AdminOnly
 */
class Test_AddNoticeWhenDelayJsAndAutoptimizeAggregateJs extends TestCase {
	public function setUp(): void {
		parent::setUp();

		$this->unregisterAllCallbacksExcept(
			'pre_update_option_wp_rocket_settings',
			'add_notice_when_delayjs_and_autoptimize_aggregatejs'
		);
	}

	public function tearDown() {
		global $wp_settings_errors;
		parent::tearDown();

		$this->restoreWpFilter( 'pre_update_option_wp_rocket_settings' );
		delete_option( 'autoptimize_js_aggregate' );
		delete_transient( 'settings_errors' );
		$wp_settings_errors = [];
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldAddExpectedNoticeToTransientErrorsArray( $config, $expected ) {
		update_option( 'autoptimize_js_aggregate', $config['autoptimizeAggregateJSActive'] );

		apply_filters( 'pre_update_option_wp_rocket_settings', $config['delayJSActiveNew'], $config['delayJSActiveOld'] );

		$settings_errors = get_settings_errors('general');

		$this->assertSame( $expected, $settings_errors );
	}

}
