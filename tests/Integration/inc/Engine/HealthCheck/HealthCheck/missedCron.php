<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\HealthCheck\HealthCheck;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\CapTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\HealthCheck\HealthCheck::missed_cron
 *
 * @group  HealthCheck
 * @group  AdminOnly
 */
class Test_MissedCron extends TestCase {
	use CapTrait;

	private $purge_cron;
	private $async_css;
	private $manual_preload;
	private $schedule_automatic_cleanup;

	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'admin_notices', 'missed_cron', 10 );
		Functions\expect( 'wp_create_nonce' )
			->with( 'rocket_ignore_rocket_warning_cron' )
			->andReturn( '123456' );
	}

	public function tear_down() {
		$this->restoreWpHook( 'admin_notices' );

		parent::tear_down();

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
	 * @dataProvider configTestData
	 */
	public function testShouldReturnNullWhenNothingToDisplay( array $config, $expected ) {
		$this->configUser( $config );
		$this->configOptions( $config );

		set_current_screen( $config['screen'] );

		foreach ( $config['events'] as $hook => $timestamp ) {
			if ( ! $timestamp ) {
				continue;
			}
			wp_schedule_single_event( $timestamp, $hook );
		}

		$expected = empty( $expected )
			? $expected
			: $this->format_the_html( $expected );

		$this->assertSame(
			$expected,
			$this->getActualHtml()
		);
	}

	protected function configUser( $config ) {
		if ( $config['cap'] ) {
			self::setAdminCap();
			$role = 'administrator';
		} else {
			$role = 'editor';
		}
		$user_id = $this->factory->user->create( [ 'role' => $role ] );

		wp_set_current_user( $user_id );

		update_user_meta( $user_id, 'rocket_boxes', $config['dismissed'] );
	}

	protected function configOptions( $config ) {
		$this->purge_cron                 = $config['options']['purge_cron'];
		$this->async_css                  = $config['options']['async_css'];
		$this->manual_preload             = $config['options']['manual_preload'];
		$this->schedule_automatic_cleanup = $config['options']['schedule_automatic_cleanup'];

		$this->disable_wp_cron = $config['disable_cron'];

		add_filter( 'pre_get_rocket_option_purge_cron_interval', [ $this, 'purge_cron' ] );
		add_filter( 'pre_get_rocket_option_async_css', [ $this, 'async_css' ] );
		add_filter( 'pre_get_rocket_option_manual_preload', [ $this, 'manual_preload' ] );
		add_filter( 'pre_get_rocket_option_schedule_automatic_cleanup', [ $this, 'schedule_automatic_cleanup' ] );
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

	private function getActualHtml() {
		ob_start();
		do_action( 'admin_notices' );
		$actual = ob_get_clean();

		return empty( $actual )
			? $actual
			: $this->format_the_html( $actual );
	}
}
