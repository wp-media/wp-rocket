<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\Tests\Integration\DBTrait;
use Brain\Monkey\Functions;

/**
 * Test class covering WP_Rocket\Engine\Admin\RocketInsights\Subscriber::maybe_display_rocket_insights_promotion_notice
 *
 * @group RocketInsights
 * @group AdminOnly
 */
class Test_MaybeDisplayRocketInsightsPromotionNotice extends TestCase {
    use DBTrait;

    public static function set_up_before_class() {
		parent::set_up_before_class();

		// Install the Performance Monitoring table.
		self::installPerformanceMonitoringTable();
	}

	public static function tear_down_after_class() {
		self::uninstallPerformanceMonitoringTable();

		parent::tear_down_after_class();
	}

	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'admin_notices', 'maybe_display_rocket_insights_promotion_notice' );
        Functions\when( 'wp_create_nonce' )->justReturn( '123456' );
	}

	public function tear_down() {
        delete_user_meta( get_current_user_id(), 'rocket_boxes', [ 'rocket_insights_promotion_notice' ] );
        $this->restoreWpHook( 'admin_notices' );

        // Clean up data after each test
		self::truncatePerformanceMonitoringTable();

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
		if ( isset( $config['role'] ) ) {
			$this->configUser( $config['role'] );
		}

		if ( $config['user_meta'] ) {
			add_user_meta( get_current_user_id(), 'rocket_boxes', [ 'rocket_insights_promotion_notice' ] );
		}

        if ( $config['is_monitored'] ) {
            $container = apply_filters( 'rocket_container', null );
			$manager   = $container->get( 'ri_manager' );

            $manager->add_to_the_queue(
                home_url( '/test' ),
                true,
                [
                    'title' => 'Test Page ',
                ]
            );
        }

		if ( $expected['should_display'] ) {
			$this->assertStringContainsStringIgnoringCase(
				$this->format_the_html( $this->config['notice'] ),
				$this->get_actual_html()
			);
		} else {
			$this->assertStringNotContainsStringIgnoringCase(
				$this->format_the_html( $this->config['notice'] ),
				$this->get_actual_html()
			);
		}
	}

	private function configUser( $role ) {
		// Make sure the capability is correct.
		$admin = get_role( 'administrator' );
		if ( ! $admin->has_cap( 'rocket_manage_options' ) ) {
			$admin->add_cap( 'rocket_manage_options' );
		}

		$user_id = $this->factory->user->create( [ 'role' => $role ] );
		wp_set_current_user( $user_id );
	}

	private function get_actual_html() {
		ob_start();
		do_action( 'admin_notices' );

		return $this->format_the_html( ob_get_clean() );
	}
}
