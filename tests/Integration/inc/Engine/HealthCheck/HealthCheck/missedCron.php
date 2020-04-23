<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\HealthCheck\HealthCheck;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\HealthCheck\HealthCheck::missed_cron
 * @group  HealthCheck
 * @group  AdminOnly
 */
class Test_MissedCron extends TestCase {
	private static $container;
	private $purge_cron;
	private $async_css;
	private $manual_preload;
	private $schedule_automatic_cleanup;

	private function getActualHtml() {
		ob_start();
		do_action( 'admin_notices' );

		return $this->format_the_html( ob_get_clean() );
	}

	public static function setUpBeforeClass() {
		self::$container = apply_filters( 'rocket_container', null );
	}

	public function tearDown() {
		parent::tearDown();

		remove_filter( 'pre_get_rocket_option_purge_cron_interval', [ $this, 'purge_cron' ] );
		remove_filter( 'pre_get_rocket_option_async_css', [ $this, 'async_css' ] );
		remove_filter( 'pre_get_rocket_option_manual_preload', [ $this, 'manual_preload' ] );
		remove_filter( 'pre_get_rocket_option_schedule_automatic_cleanup', [ $this, 'schedule_automatic_cleanup' ] );

		wp_clear_scheduled_hook( 'rocket_purge_time_event' );
		wp_clear_scheduled_hook( 'rocket_database_optimization_time_event' );
		wp_clear_scheduled_hook( 'rocket_database_optimization_cron_interval' );
		wp_clear_scheduled_hook( 'rocket_preload_cron_interval' );
		wp_clear_scheduled_hook( 'rocket_critical_css_generation_cron_interval' );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnNullWhenNothingToDisplay( $config ) {
		if ( $config['cap'] ) {
			$admin = get_role( 'administrator' );
			$admin->add_cap( 'rocket_manage_options' );

			$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );
		} else {
			$user_id = self::factory()->user->create( [ 'role' => 'editor' ] );
		}

		wp_set_current_user( $user_id );
		set_current_screen( $config['screen'] );
		update_user_meta( $user_id, 'rocket_boxes', $config['dismissed'] );

		$this->purge_cron = $config['options']['purge_cron'];
		$this->async_css = $config['options']['async_css'];
		$this->manual_preload = $config['options']['manual_preload'];
		$this->schedule_automatic_cleanup = $config['options']['schedule_automatic_cleanup'];

		foreach ( $config['events'] as $hook => $timestamp ) {
			if ( ! $timestamp ) {
				continue;
			}

			wp_schedule_single_event( $timestamp, $hook );
		}

		add_filter( 'pre_get_rocket_option_purge_cron_interval', [ $this, 'purge_cron' ] );
		add_filter( 'pre_get_rocket_option_async_css', [ $this, 'async_css' ] );
		add_filter( 'pre_get_rocket_option_manual_preload', [ $this, 'manual_preload' ] );
		add_filter( 'pre_get_rocket_option_schedule_automatic_cleanup', [ $this, 'schedule_automatic_cleanup' ] );

		Functions\expect( 'rocket_get_constant' )
			->atMost()
			->times( 1 )
			->with( 'DISABLE_WP_CRON' )
			->andReturn( $config['disable_cron'] );

		Functions\expect( 'wp_create_nonce' )
			->atMost()
			->times( 1 )
			->with( 'rocket_ignore_rocket_warning_cron' )
			->andReturn( '123456' );

		if ( empty( $config['expected'] ) ) {
			$this->assertNull( self::$container->get( 'health_check' )->missed_cron() );
		} else {
			$this->assertContains(
				$this->format_the_html( $config['expected'] ),
				$this->getActualHtml()
			);
		}
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'missed-cron' );
	}

	public function purge_cron() {
		return $this->purge_cron;
	}

	public function async_css() {
		return $this->async_css;
	}

	public function schedule_automatic_cleanup() {
		return $this->schedule_automatic_cleanup;
	}

	public function manual_preload() {
		return $this->manual_preload;
	}
}
