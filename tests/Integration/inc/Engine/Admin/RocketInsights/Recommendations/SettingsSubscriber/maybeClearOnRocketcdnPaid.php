<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Recommendations\SettingsSubscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\Recommendations\SettingsSubscriber::maybe_clear_on_rocketcdn_paid
 *
 * @group RocketInsights
 * @group Recommendations
 * @group AdminOnly
 */
class Test_MaybeClearOnRocketcdnPaid extends TestCase {
	/**
	 * Recommendations transient name.
	 *
	 * @var string
	 */
	private const TRANSIENT = 'wpr_ri_recommendations';

	/**
	 * Set up before each test.
	 */
	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'update_option_wp_rocket_settings', 'maybe_clear_on_rocketcdn_paid', 9 );
	}

	/**
	 * Tear down after each test.
	 */
	public function tear_down() {
		delete_transient( self::TRANSIENT );
		$this->restoreWpHook( 'update_option_wp_rocket_settings' );

		parent::tear_down();
	}

	/**
	 * Test that the transient is deleted only on a transition into the paid state.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Whether the transient is expected to be deleted.
	 */
	public function testShouldClearTransientOnlyOnTransitionToPaid( $config, $expected ) {
		set_transient(
			self::TRANSIENT,
			[
				'status'    => 'completed',
				'timestamp' => time(),
				'data'      => [],
			],
			HOUR_IN_SECONDS
		);

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core option hook.
		do_action( 'update_option_wp_rocket_settings', $config['old_options'], $config['new_options'] );

		if ( $expected ) {
			$this->assertFalse( get_transient( self::TRANSIENT ) );
		} else {
			$this->assertNotFalse( get_transient( self::TRANSIENT ) );
		}
	}
}
