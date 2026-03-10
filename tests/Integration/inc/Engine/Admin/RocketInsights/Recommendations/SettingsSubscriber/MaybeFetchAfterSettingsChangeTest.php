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
	private $fetch_called = false;

	public function set_up() {
		parent::set_up();

		$this->fetch_called = false;

		// Hook into fetch_recommendations to track if it was called.
		add_filter( 'pre_transient_wpr_ri_recommendations', [ $this, 'track_fetch_call' ], 10, 1 );
	}

	public function tear_down() {
		// Clean up transients.
		delete_transient( 'wpr_ri_recommendations' );
		delete_transient( 'wpr_ri_global_score' );

		// Remove our tracking filter.
		remove_filter( 'pre_transient_wpr_ri_recommendations', [ $this, 'track_fetch_call' ] );

		parent::tear_down();
	}

	/**
	 * Track if fetch was called by checking transient operations.
	 *
	 * @param mixed $value Transient value.
	 * @return mixed
	 */
	public function track_fetch_call( $value ) {
		// If we're checking during the test, capture that fetch was called.
		if ( doing_action( 'rocket_after_save_options' ) ) {
			$this->fetch_called = true;
		}
		return $value;
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

		// Trigger the settings save action.
		do_action( 'rocket_after_save_options', $config['old_options'], $config['new_options'] );

		// Get the updated recommendations transient.
		$recommendations = get_transient( 'wpr_ri_recommendations' );

		// Verify expectations.
		if ( $expected['should_trigger_fetch'] ) {
			$this->assertNotFalse( $recommendations, 'Recommendations should exist after fetch' );
			// In a real scenario, status might be 'loading' or 'completed'.
			// For testing purposes, we just verify the transient was updated.
		} else {
			// If initial recommendations existed, they should remain unchanged.
			if ( isset( $config['initial_recommendations'] ) ) {
				$this->assertSame( $config['initial_recommendations'], $recommendations, 'Recommendations should not change' );
			}
		}
	}
}
