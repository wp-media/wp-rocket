<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Recommendations\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering WP_Rocket\Engine\Admin\RocketInsights\Recommendations\Subscriber::force_global_metrics_recalculation
 *
 * @group RocketInsights
 * @group Recommendations
 * @group AdminOnly
 */
class Test_ForceGlobalMetricsRecalculation extends TestCase {
	/**
	 * Transient name for global score data.
	 *
	 * @var string
	 */
	private const GLOBAL_SCORE_TRANSIENT = 'wpr_global_score_data';

	/**
	 * Set up before each test.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		// Clear transient before each test.
		delete_transient( self::GLOBAL_SCORE_TRANSIENT );

        $this->unregisterAllCallbacksExcept( 'wp_rocket_upgrade', 'force_global_metrics_recalculation', 10 );
	}

	/**
	 * Tear down after each test.
	 *
	 * @return void
	 */
	public function tear_down() {
		// Clear transient after each test.
		delete_transient( self::GLOBAL_SCORE_TRANSIENT );

        $this->restoreWpHook( 'wp_rocket_upgrade' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
		// Set up the transient if configured.
		if ( ! empty( $config['transient_value'] ) ) {
			set_transient( self::GLOBAL_SCORE_TRANSIENT, $config['transient_value'] );
		}

		// Verify transient is set before the hook.
		$transient_before = get_transient( self::GLOBAL_SCORE_TRANSIENT );
		$this->assertSame( $expected['transient_before'], $transient_before );

		// Fire the wp_rocket_upgrade hook.
		do_action( 'wp_rocket_upgrade', $config['new_version'], $config['old_version'] );

		// Check if transient was deleted or kept.
		$transient_after = get_transient( self::GLOBAL_SCORE_TRANSIENT );
		$this->assertSame( $expected['transient_after'], $transient_after );
	}
}
