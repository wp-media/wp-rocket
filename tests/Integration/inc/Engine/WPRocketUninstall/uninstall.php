<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\WPRocketUninstall;

use WPRocketUninstall;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers WPRocketUninstall::uninstall
 * @group  Uninstall
 * @group  vfs
 */
class Test_Uninstall extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/WPRocketUninstall/uninstall.php';

	private $uninstall;

	private $options = [
		'wp_rocket_settings',
		'rocket_analytics_notice_displayed',
		'rocketcdn_user_token',
		'rocketcdn_process',
	];

	private $transients = [
		'wp_rocket_customer_data',
		'rocket_notice_missing_tags',
		'rocket_clear_cache',
		'rocket_check_key_errors',
		'rocket_send_analytics_data',
		'rocket_critical_css_generation_process_running',
		'rocket_critical_css_generation_process_complete',
		'rocket_critical_css_generation_triggered',
		'rocketcdn_status',
		'rocketcdn_pricing',
		'rocketcdn_purge_cache_response',
		'rocket_cloudflare_ips',
		'rocket_cloudflare_is_api_keys_valid',
		'rocket_preload_triggered',
		'rocket_preload_complete',
		'rocket_preload_complete_time',
		'rocket_preload_errors',
		'rocket_database_optimization_process',
		'rocket_database_optimization_process_complete',
	];

	private $events = [
		'rocket_purge_time_event',
		'rocket_database_optimization_time_event',
		'rocket_google_tracking_cache_update',
		'rocket_facebook_tracking_cache_update',
		'rocket_cache_dir_size_check',
		'rocketcdn_check_subscription_status_event',
		'rocket_cron_deactivate_cloudflare_devmode',
	];

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_PLUGIN_ROOT . '/inc/Engine/WPRocketUninstall.php';
	}

	public function setUp() {
		parent::setUp();

		foreach ( $this->options as $option ) {
			add_option( $option, 'test' );
		}

		foreach ( $this->transients as $transient ) {
			set_transient( $transient, '', HOUR_IN_SECONDS );
		}

		foreach( $this->events as $event ) {
			wp_schedule_event( time() + 3600, 'hourly', $event );
		}

		$this->uninstall = new WPRocketUninstall( $this->filesystem->getUrl( 'wp-content/cache'), $this->filesystem->getUrl( 'wp-content/wp-rocket-config' ) );
	}

	public function tearDown() {
		array_walk( $this->options, 'delete_option' );
		array_walk( $this->transients, 'delete_transient' );

		foreach ( $this->events as $event ) {
			wp_clear_scheduled_hook( $event );
		}

		parent::tearDown();
	}

	public function testShouldDeleteAll() {
		$this->uninstall->uninstall();

		foreach ( $this->options as $option ) {
			$this->assertFalse( get_option( $option ) );
		}

		foreach ( $this->transients as $transient ) {
			$this->assertFalse( get_transient( $transient ) );
		}

		foreach( $this->events as $event ) {
			$this->assertFalse( wp_next_scheduled( $event ) );
		}

		$this->assertNull( $this->filesystem->getDir( 'wp-content/cache/wp-rocket' ) );
		$this->assertNull( $this->filesystem->getDir( 'wp-content/cache/busting' ) );
		$this->assertNull( $this->filesystem->getDir( 'wp-content/cache/critical-css' ) );
		$this->assertNull( $this->filesystem->getDir( 'wp-content/cache/min' ) );
		$this->assertNull( $this->filesystem->getDir( 'wp-content/wp-rocket-config' ) );
	}
}
