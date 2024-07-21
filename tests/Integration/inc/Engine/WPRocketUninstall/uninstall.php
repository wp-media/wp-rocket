<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\WPRocketUninstall;

use WPRocketUninstall;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * Test class covering WPRocketUninstall::uninstall
 *
 * @group  AdminOnly
 * @group Uninstall
 * @group vfs
 */
class Test_Uninstall extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/WPRocketUninstall/uninstall.php';

	private static $options = [
		'wp_rocket_settings'                => null,
		'rocket_analytics_notice_displayed' => null,
		'rocketcdn_user_token'              => null,
		'rocketcdn_process'                 => null,
		'wp_rocket_hide_deactivation_form'  => null,
		'wp_rocket_last_base_url'           => null,
		'wp_rocket_no_licence'              => null,
		'wp_rocket_last_option_hash'        => null,
		'wp_rocket_debug'                   => null,
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
		'rocket_preload_previous_requests_durations'      => null,
		'rocket_preload_check_duration'                   => null,
		'rocket_database_optimization_process'            => null,
		'rocket_database_optimization_process_complete'   => null,
		'rocket_hide_deactivation_form'                   => null,
		'wpr_preload_running'                             => null,
		'rocket_preload_as_tables_count'                  => null,
		'wpr_dynamic_lists'                               => null,
		'wpr_dynamic_lists_delayjs'                       => null,
		'rocket_domain_changed'                           => null,
		'wp_rocket_rucss_errors_count'                    => null,
		'wpr_dynamic_lists_incompatible_plugins'          => null,
		'rocket_divi_notice'                              => null,
		'rocket_saas_processing'                          => null,
		'rocket_mod_pagespeed_enabled'                    => null,
		'wp_rocket_pricing'                               => null,
		'wp_rocket_pricing_timeout'                       => null,
		'wp_rocket_pricing_timeout_active'                => null,
		'rocket_get_refreshed_fragments_cache'            => null,
		'wpr_user_information_timeout_active'             => null,
		'wpr_user_information_timeout'                    => null,
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
		foreach ( self::$options as $option_name => $value ) {
			if ( ! empty( $value ) ) {
				update_option( $option_name, $value );
			} else {
				delete_option( $option_name );
			}
		}

		parent::tear_down_after_class();
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
		$cache_path          = 'vfs://public/wp-content/cache/';
		$config_path         = 'vfs://public/wp-content/wp-rocket-config/';
		$container           = apply_filters( 'rocket_container', null );
		$rucss_usedcss_table = $container->get( 'rucss_usedcss_table' );
		$preload_table       = $container->get( 'preload_caches_table' );
		$atf_table           = $container->get( 'atf_table' );

		$uninstall = new WPRocketUninstall( $cache_path, $config_path, $rucss_usedcss_table, $preload_table, $atf_table );

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
	}
}
