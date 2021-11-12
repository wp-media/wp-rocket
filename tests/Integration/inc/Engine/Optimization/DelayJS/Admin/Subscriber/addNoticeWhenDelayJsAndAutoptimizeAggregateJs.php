<?php

declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\DelayJS\Admin\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DelayJS\Admin\Subscriber::add_notice_when_delayjs_and_autoptimize_aggreatejs
 *
 * @group DelayJS
 * @group AdminOnly
 * @group cgtest
 */
class Test_AddNoticeWhenDelayJsAndAutoptimizeAggregateJs extends TestCase {
	public function setUp(): void {
		parent::setUp();

		$this->unregisterAllCallbacksExcept(
			'pre_update_option_wp_rocket_settings',
			'add_notice_when_delayjs_and_autoptimize_aggregatejs',
			11
		);

		set_transient( 'settings_errors', [] );
	}

	public function tearDown() {
		parent::tearDown();

		$this->restoreWpFilter( 'pre_update_option_wp_rocket_settings' );
		set_transient( 'settings_errors', [] );
		delete_option( 'autoptimize_js_aggregate' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldAddExpectedNoticeToTransientErrorsArray( $config, $expected ) {
		update_option( 'autoptimize_js_aggregate', $config['autoptimizeAggregateJSActive'] );

		apply_filters( 'pre_update_option_wp_rocket_settings', $config['delayJSActiveNew'], $config['delayJSActiveOld'] );

		$transient_errors = get_transient( 'settings_errors' );

		$this->assertSame( $expected, $transient_errors );
	}

}
