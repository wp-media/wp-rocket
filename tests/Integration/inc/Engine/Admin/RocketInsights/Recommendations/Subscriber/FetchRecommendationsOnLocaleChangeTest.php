<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Recommendations\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering WP_Rocket\Engine\Admin\RocketInsights\Recommendations\Subscriber::fetch_recommendations_on_locale_change
 *
 * @group RocketInsights
 * @group Recommendations
 * @group AdminOnly
 */
class Test_FetchRecommendationsOnLocaleChange extends TestCase {
	/**
	 * Transient name for recommendations.
	 *
	 * @var string
	 */
	private const RECOMMENDATIONS_TRANSIENT = 'wpr_ri_recommendations';

	/**
	 * User ID for testing.
	 *
	 * @var int
	 */
	private $user_id;

	/**
	 * Set up before each test.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		// Clear transient before each test.
		delete_transient( self::RECOMMENDATIONS_TRANSIENT );

		// Create a test user.
		$this->user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );

		$this->unregisterAllCallbacksExcept( 'updated_user_meta', 'fetch_recommendations_on_locale_change', 10 );
	}

	/**
	 * Tear down after each test.
	 *
	 * @return void
	 */
	public function tear_down() {
		// Clear transient after each test.
		delete_transient( self::RECOMMENDATIONS_TRANSIENT );

		// Clean up the test user.
		if ( $this->user_id ) {
			wp_delete_user( $this->user_id );
		}

		$this->restoreWpHook( 'updated_user_meta' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
		// Set up the initial transient if configured.
		if ( ! empty( $config['initial_transient'] ) ) {
			set_transient( self::RECOMMENDATIONS_TRANSIENT, $config['initial_transient'] );
		}

		// Fire the updated_user_meta hook.
		do_action( 'updated_user_meta', 1, $this->user_id, $config['meta_key'] );

		// Check transient status.
		$transient_after = get_transient( self::RECOMMENDATIONS_TRANSIENT );

		if ( $expected['should_trigger_fetch'] ) {
			$this->assertIsArray( $transient_after );
            return;
		} 

        // When fetch is not triggered, transient should remain unchanged.
		$this->assertSame( $expected['transient_after'], $transient_after );
	}
}
