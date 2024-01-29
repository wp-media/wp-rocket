<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\WPRocketUninstall;

use WPRocketUninstall;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers WPRocketUninstall::uninstall
 *
 * @group  AdminOnly
 * @group  Uninstall
 * @group  vfs
 */
class Test_Uninstall extends FilesystemTestCase {

	protected $path_to_test_data = '/inc/Engine/WPRocketUninstall/uninstall.php';

	private static $options = [
		'wp_rocket_settings'                => null,
		'rocket_analytics_notice_displayed' => null,
		'rocketcdn_user_token'              => null,
		'rocketcdn_process'                 => null,
	];

	protected static $transients = [
		'wp_rocket_customer_data'                         => null,
		'rocket_notice_missing_tags'                      => null,
		'rocket_clear_cache'                              => null,
		'rocket_check_key_errors'                         => null,
		'rocket_send_analytics_data'                      => null,
		'rocket_critical_css_generation_process_running'  => null,
		'rocket_critical_css_generation_process_complete' => null,
		'rocket_critical_css_generation_triggered'        => null,
		'rocketcdn_status'                                => null,
		'rocketcdn_pricing'                               => null,
		'rocketcdn_purge_cache_response'                  => null,
		'rocket_cloudflare_ips'                           => null,
		'rocket_cloudflare_is_api_keys_valid'             => null,
		'rocket_preload_triggered'                        => null,
		'rocket_preload_complete'                         => null,
		'rocket_preload_complete_time'                    => null,
		'rocket_preload_errors'                           => null,
		'rocket_preload_previous_request_durations'       => null,
		'rocket_database_optimization_process'            => null,
		'rocket_database_optimization_process_complete'   => null,
	];

	private $events = [
		'rocket_purge_time_event',
		'rocket_database_optimization_time_event',
		'rocket_cache_dir_size_check',
		'rocketcdn_check_subscription_status_event',
		'rocket_cron_deactivate_cloudflare_devmode',
	];

	public static function set_up_before_class() {
		parent::set_up_before_class();

		require_once WP_ROCKET_PLUGIN_ROOT . '/inc/Engine/WPRocketUninstall.php';

		foreach ( self::getOptionNames() as $option_name ) {
			self::$options[ $option_name ] = get_option( $option_name );
		}
	}

	public static function tear_down_after_class() {
		parent::set_up_before_class();

		foreach ( self::$options as $option_name => $value ) {
			if ( ! empty( $value ) ) {
				update_option( $option_name, $value );
			} else {
				delete_option( $option_name );
			}
		}
	}

	private static function getOptionNames() {
		return array_keys( self::$options );
	}

	private static function getTransientNames() {
		return array_keys( self::$transients );
	}

	public function set_up() {
		parent::set_up();

		foreach ( self::getOptionNames() as $option_name ) {
			add_option( $option_name, 'test' );
		}

		foreach ( self::getTransientNames() as $transient ) {
			set_transient( $transient, '', HOUR_IN_SECONDS );
		}

		foreach ( $this->events as $event ) {
			wp_schedule_event( time() + 3600, 'hourly', $event );
		}
	}

	public function tear_down() {
		foreach ( self::getOptionNames() as $option_name ) {
			delete_option( $option_name );
		}
		foreach ( self::getTransientNames() as $transient ) {
			delete_transient( $transient );
		}
		foreach ( $this->events as $event ) {
			wp_clear_scheduled_hook( $event );
		}

		parent::tear_down();
	}

	public function testShouldDeleteAll() {
    $cache_path            = 'vfs://public/wp-content/cache/';
		$config_path           = 'vfs://public/wp-content/wp-rocket-config/';
		$container             = apply_filters( 'rocket_container', null );
		$rucss_usedcss_table   = $container->get( 'rucss_usedcss_table' );
		$preload_table         = $container->get( 'preload_caches_table' );

		$uninstall = new WPRocketUninstall( $cache_path, $config_path, $rucss_usedcss_table, $preload_table );

		$uninstall->uninstall();

		foreach ( self::getOptionNames() as $option_name ) {
			$this->assertFalse( get_option( $option_name ) );
		}

		foreach ( self::getTransientNames() as $transient ) {
			$this->assertFalse( get_transient( $transient ) );
		}

		foreach ( $this->events as $event ) {
			$this->assertFalse( wp_next_scheduled( $event ) );
		}

		$this->assertEmpty( $this->filesystem->getListing( $cache_path ) );
		$this->assertFalse( $this->filesystem->exists( $config_path ) );

		$this->assertFalse( $rucss_usedcss_table->exists() );
	}
}
