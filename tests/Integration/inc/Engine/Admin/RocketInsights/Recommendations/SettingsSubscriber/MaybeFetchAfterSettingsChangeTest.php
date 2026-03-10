<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Recommendations\SettingsSubscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class for SettingsSubscriber integration with WordPress hooks
 *
 * @group RocketInsights
 * @group AdminOnly
 */
class MaybeFetchAfterSettingsChangeTest extends TestCase {
protected $path_to_test_data = '/inc/Engine/Admin/RocketInsights/Recommendations/SettingsSubscriber/MaybeFetchAfterSettingsChangeIntegrationTest.php';

	public function set_up() {
		parent::set_up();
	}

	public function tear_down() {
		// Clean up transients.
		delete_transient( 'wpr_ri_recommendations' );
		delete_transient( 'wpr_ri_global_score' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldHandleSettingsChanges( $config, $expected ) {
		// Set up initial recommendation status.
		if ( isset( $config['initial_recommendations'] ) ) {
			set_transient( 'wpr_ri_recommendations', $config['initial_recommendations'], DAY_IN_SECONDS );
		}

		// Set up global score data.
		if ( isset( $config['global_score_data'] ) ) {
			set_transient( 'wpr_ri_global_score', $config['global_score_data'], DAY_IN_SECONDS );
		}

		// Set up initial WP Rocket options (simulating the old options state).
		update_option( WP_ROCKET_SLUG, $config['old_options'] );

		// Store initial state for comparison.
		$initial_recommendations = get_transient( 'wpr_ri_recommendations' );
		$initial_timestamp       = isset( $initial_recommendations['timestamp'] ) ? $initial_recommendations['timestamp'] : 0;
		$initial_hash            = isset( $initial_recommendations['metrics_hash'] ) ? $initial_recommendations['metrics_hash'] : '';

		// Trigger the real settings save flow by updating option to new value.
		// This will fire update_option_wp_rocket_settings → rocket_after_save_options() → do_action('rocket_after_save_options').
		update_option( WP_ROCKET_SLUG, $config['new_options'] );

		// Get the updated recommendations transient.
		$recommendations = get_transient( 'wpr_ri_recommendations' );

		// Verify expectations.
		if ( $expected['should_trigger_fetch'] ) {
			$this->assertNotFalse( $recommendations, 'Recommendations should exist after fetch' );
			
			// Verify that a fetch actually occurred by checking if timestamp or metrics_hash changed.
			if ( $initial_timestamp > 0 ) {
				$new_timestamp = isset( $recommendations['timestamp'] ) ? $recommendations['timestamp'] : 0;
				$new_hash      = isset( $recommendations['metrics_hash'] ) ? $recommendations['metrics_hash'] : '';
				
				// At least one of these should have changed if a fetch occurred.
				$has_changed = ( $new_timestamp !== $initial_timestamp ) || ( $new_hash !== $initial_hash );
				$this->assertTrue( $has_changed, 'Fetch should update timestamp or metrics_hash' );
			}
		} else {
			// If initial recommendations existed, they should remain unchanged.
			if ( isset( $config['initial_recommendations'] ) ) {
				$this->assertSame( $initial_timestamp, isset( $recommendations['timestamp'] ) ? $recommendations['timestamp'] : 0, 'Timestamp should not change when fetch is not triggered' );
				$this->assertSame( $initial_hash, isset( $recommendations['metrics_hash'] ) ? $recommendations['metrics_hash'] : '', 'Hash should not change when fetch is not triggered' );
			}
		}
	}
}
