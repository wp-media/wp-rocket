<?php

use WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\UsedCSS;

use WP_Rocket\Engine\Preload\Database\Tables\Cache;

/**
 * Manages the deletion of WP Rocket data and files on uninstall.
 */
class WPRocketUninstall {

	/**
	 * Path to the cache folder.
	 *
	 * @var string
	 */
	private $cache_path;

	/**
	 * Path to the config folder.
	 *
	 * @var string
	 */
	private $config_path;

	/**
	 * WP Rocket options.
	 *
	 * @var array
	 */
	private $options = [
		'wp_rocket_settings',
		'rocket_analytics_notice_displayed',
		'rocketcdn_user_token',
		'rocketcdn_process',
		'wp_rocket_hide_deactivation_form',
	];

	/**
	 * WP Rocket transients.
	 *
	 * @var array
	 */
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
		'wp_rocket_no_licence',
		'rocket_hide_deactivation_form',
		'wpr_preload_running',
		'rocket_preload_as_tables_count',
		'wpr_dynamic_lists',
	];

	/**
	 * WP Rocket scheduled events.
	 *
	 * @var array
	 */
	private $events = [
		'rocket_purge_time_event',
		'rocket_database_optimization_time_event',
		'rocket_cache_dir_size_check',
		'rocketcdn_check_subscription_status_event',
		'rocket_cron_deactivate_cloudflare_devmode',
	];

	/**
	 * WP Rocket cache directories.
	 *
	 * @var array
	 */
	private $cache_dirs = [
		'wp-rocket',
		'min',
		'busting',
		'critical-css',
		'used-css',
	];

	/**
	 * WP Rocket Post MetaData Entries
	 *
	 * @var array
	 */
	private $post_meta = [
		'minify_css',
		'minify_js',
		'cdn',
		'lazyload',
		'lazyload_iframes',
		'async_css',
		'defer_all_js',
		'delay_js',
		'remove_unused_css',
	];

	/**
	 * Instance of RUCSS used_css table.
	 *
	 * @var WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\UsedCSS
	 */
	private $rucss_usedcss_table;

	/**
	 * Instance of Preload rocket_cache table.
	 *
	 * @var Cache
	 */
	private $rocket_cache;

	/**
	 * Constructor.
	 *
	 * @param string  $cache_path            Path to the cache folder.
	 * @param string  $config_path           Path to the config folder.
	 * @param UsedCSS $rucss_usedcss_table   RUCSS used_css table.
	 * @param Cache   $rocket_cache   Preload rocket_cache table.
	 */
	public function __construct( $cache_path, $config_path, $rucss_usedcss_table, $rocket_cache ) {
		$this->cache_path          = trailingslashit( $cache_path );
		$this->config_path         = $config_path;
		$this->rucss_usedcss_table = $rucss_usedcss_table;
		$this->rocket_cache        = $rocket_cache;
	}

	/**
	 * Deletes all plugin data and files on uninstall.
	 *
	 * @since 3.5.2
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function uninstall() {
		$this->delete_plugin_data();
		$this->delete_cache_files();
		$this->delete_config_files();
		$this->drop_rucss_database_tables();
		$this->delete_preload_table();
	}

	/**
	 * Drop RUCSS database tables.
	 *
	 * @return void
	 */
	private function drop_rucss_database_tables() {
		// If the table exist, then drop the table.
		$this->drop_rucss_current_site_tables();

		if ( ! is_multisite() ) {
			return;
		}

		foreach ( get_sites( [ 'fields' => 'ids' ] ) as $site_id ) {
			switch_to_blog( $site_id );

			$this->drop_rucss_current_site_tables();

			restore_current_blog();
		}

	}

	/**
	 * Drop RUCSS tables for current active site.
	 */
	private function drop_rucss_current_site_tables() {
		if ( $this->rucss_usedcss_table->exists() ) {
			$this->rucss_usedcss_table->uninstall();
		}
	}

	/**
	 * Deletes WP Rocket options, transients and events.
	 *
	 * @since 3.5.2
	 * @author Remy Perona
	 *
	 * @return void
	 */
	private function delete_plugin_data() {
		delete_site_transient( 'wp_rocket_update_data' );

		// Delete all user meta related to WP Rocket.
		delete_metadata( 'user', '', 'rocket_boxes', '', true );

		// Delete all post meta related to WP Rocket.
		foreach ( $this->post_meta as $post_meta ) {
			delete_post_meta_by_key( "_rocket_exclude_{$post_meta}" );
		}

		array_walk( $this->transients, 'delete_transient' );
		array_walk( $this->options, 'delete_option' );

		foreach ( $this->events as $event ) {
			wp_clear_scheduled_hook( $event );
		}
	}

	/**
	 * Deletes all WP Rocket cache files.
	 *
	 * @since 3.5.2
	 * @author Remy Perona
	 *
	 * @return void
	 */
	private function delete_cache_files() {
		foreach ( $this->cache_dirs as $dir ) {
			$this->delete( $this->cache_path . $dir );
		}
	}

	/**
	 * Deletes all WP Rocket config files.
	 *
	 * @since 3.5.2
	 * @author Remy Perona
	 *
	 * @return void
	 */
	private function delete_config_files() {
		$this->delete( $this->config_path );
	}

	/**
	 * Drop preload tables.
	 */
	private function delete_preload_table() {
		// If the table exist, then drop the table.

		if ( $this->rocket_cache->exists() ) {
			$this->rocket_cache->uninstall();
		}

		if ( ! is_multisite() ) {
			return;
		}

		foreach ( get_sites( [ 'fields' => 'ids' ] ) as $site_id ) {
			switch_to_blog( $site_id );

			if ( $this->rocket_cache->exists() ) {
				$this->rocket_cache->uninstall();
			}

			restore_current_blog();
		}

	}

	/**
	 * Recursively deletes files and directories.
	 *
	 * @since 3.5.2
	 * @author Remy Perona
	 *
	 * @param string $file Path to file or directory.
	 */
	private function delete( $file ) {
		if ( ! is_dir( $file ) ) {
			@unlink( $file ); //phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			return;
		}

		try {
			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $file, FilesystemIterator::SKIP_DOTS ),
				RecursiveIteratorIterator::CHILD_FIRST
			);
		} catch ( UnexpectedValueException $e ) {
			return;
		}

		foreach ( $iterator as $item ) {
			if ( $item->isDir() ) {
				@rmdir( $item ); //phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

				continue;
			}

			@unlink( $item ); //phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		}

		@rmdir( $file ); //phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	}
}
