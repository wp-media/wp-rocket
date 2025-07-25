<?php

use WP_Rocket\Dependencies\BerlinDB\Database\Table;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\UsedCSS;
use WP_Rocket\Engine\Preload\Database\Tables\Cache;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Tables\AboveTheFold;
use WP_Rocket\Engine\Optimization\LazyRenderContent\Database\Table\LazyRenderContent;
use WP_Rocket\Engine\Media\PreloadFonts\Database\Table\PreloadFonts;
use WP_Rocket\Engine\Media\PreconnectExternalDomains\Database\Table\PreconnectExternalDomains;

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
		'wp_rocket_last_base_url',
		'wp_rocket_no_licence',
		'wp_rocket_last_option_hash',
		'wp_rocket_debug',
		'wp_rocket_rocketcdn_old_url',
		'plugin_family_dismiss_promote_imagify',
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
		'rocket_hide_deactivation_form',
		'wpr_preload_running',
		'rocket_preload_as_tables_count',
		'wpr_dynamic_lists',
		'wpr_dynamic_lists_delayjs',
		'rocket_domain_changed',
		'wp_rocket_rucss_errors_count',
		'wpr_dynamic_lists_incompatible_plugins',
		'rocket_divi_notice',
		'rocket_saas_processing',
		'rocket_mod_pagespeed_enabled',
		'wp_rocket_pricing',
		'wp_rocket_pricing_timeout',
		'wp_rocket_pricing_timeout_active',
		'rocket_get_refreshed_fragments_cache',
		'rocket_preload_previous_requests_durations',
		'rocket_preload_check_duration',
		'wpr_user_information_timeout_active',
		'wpr_user_information_timeout',
		'rocket_fonts_data_collection',
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
		'fonts',
		'background-css',
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
		'lazyload_css_bg_img',
	];

	/**
	 * Tables instances
	 *
	 * @var array
	 */
	private $tables;

	/**
	 * Constructor.
	 *
	 * @param string                    $cache_path            Path to the cache folder.
	 * @param string                    $config_path           Path to the config folder.
	 * @param UsedCSS                   $rucss_usedcss_table   RUCSS used_css table.
	 * @param Cache                     $rocket_cache          Preload rocket_cache table.
	 * @param AboveTheFold              $atf_table             Above the fold table.
	 * @param LazyRenderContent         $lrc_table Lazy Render content table.
	 * @param PreloadFonts              $preload_fonts_table   Preload fonts table.
	 * @param PreconnectExternalDomains $preload_domains_table Preload External Domains content table.
	 */
	public function __construct(
		$cache_path,
		$config_path,
		$rucss_usedcss_table,
		$rocket_cache,
		$atf_table,
		$lrc_table,
		$preload_fonts_table,
		$preload_domains_table
	) {
		$this->cache_path  = trailingslashit( $cache_path );
		$this->config_path = $config_path;
		$this->tables      = [
			$rucss_usedcss_table,
			$rocket_cache,
			$atf_table,
			$lrc_table,
			$preload_fonts_table,
			$preload_domains_table,
		];
	}

	/**
	 * Deletes all plugin data and files on uninstall.
	 *
	 * @since 3.5.2
	 *
	 * @return void
	 */
	public function uninstall() {
		$this->delete_plugin_data();
		$this->delete_cache_files();
		$this->delete_config_files();

		foreach ( $this->tables as $table ) {
			$this->delete_table( $table );
		}
	}

	/**
	 * Deletes a table
	 *
	 * @param Table $table Table instance.
	 *
	 * @return void
	 */
	private function delete_table( $table ) {
		if ( $table->exists() ) {
			$table->uninstall();
		}

		if ( ! is_multisite() ) {
			return;
		}

		foreach ( get_sites( [ 'fields' => 'ids' ] ) as $site_id ) {
			switch_to_blog( $site_id );

			if ( $table->exists() ) {
				$table->uninstall();
			}

			restore_current_blog();
		}
	}

	/**
	 * Deletes WP Rocket options, transients and events.
	 *
	 * @since 3.5.2
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
	 *
	 * @return void
	 */
	private function delete_config_files() {
		$this->delete( $this->config_path );
	}

	/**
	 * Recursively deletes files and directories.
	 *
	 * @since 3.5.2
	 *
	 * @param string $file Path to file or directory.
	 */
	private function delete( $file ) {
		if ( ! is_dir( $file ) ) {
			wp_delete_file( $file );
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
				@rmdir( $item ); //phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_operations_rmdir

				continue;
			}

			wp_delete_file( $item );
		}

		@rmdir( $file ); //phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_operations_rmdir
	}
}
