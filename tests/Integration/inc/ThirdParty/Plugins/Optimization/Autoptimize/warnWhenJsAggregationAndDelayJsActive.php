<?php

declare( strict_types=1 );

use WP_Rocket\Tests\Integration\CapTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers WP_Rocket\ThirdParty\Plugins\Optimization\Autoptimize::warn_when_js_aggregation_and_delay_js_active
 *
 * @group  Autoptimize
 * @group  ThirdParty
 */
class Test_WarnWhenJsAggregationAndDelayJsActive extends TestCase {
	use CapTrait;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		CapTrait::setAdminCap();
	}

	public function setUp(): void {
		parent::setUp();

		$this->unregisterAllCallbacksExcept(
			'admin_notices',
			'warn_when_js_aggregation_and_delay_js_active'
		);
	}

	public function tearDown() {
		parent::tearDown();

		$this->restoreWpFilter( 'admin_notices' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldAddExpectedNotice( $config, $expected ) {
		$this->constants['AUTOPTIMIZE_PLUGIN_VERSION'] = '1.2.3';
		$this->stubRocketGetConstant();

		$current_user = static::factory()->user->create( [ 'role' => 'administrator' ] );
		set_current_user( $current_user );

		update_option( 'autoptimize_aggregate_js', 'on' );
		add_filter( 'pre_get_rocket_option_delay_js', function () {
			return true;
		} );

		ob_start();
		do_action( 'admin_notices' );
		$notices = ob_get_clean();

		$this->assertStringContainsString( $expected, $notices );
	}
}
