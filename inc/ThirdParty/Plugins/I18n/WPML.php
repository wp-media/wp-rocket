<?php

namespace WP_Rocket\ThirdParty\Plugins\I18n;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Filesystem_Direct;
use WP_Rocket\ThirdParty\ReturnTypesTrait;

/**
 * Subscriber for compatibility with WPML.
 */
class WPML implements Subscriber_Interface {
	use ReturnTypesTrait;

	/**
	 * Filesystem instance.
	 *
	 * @var WP_Filesystem_Direct
	 */
	protected $filesystem;

	/**
	 * Instantiate class.
	 *
	 * @param WP_Filesystem_Direct $filesystem Filesystem instance.
	 */
	public function __construct( WP_Filesystem_Direct $filesystem = null ) {
		$this->filesystem = $filesystem ?: rocket_direct_filesystem();
	}


	/**
	 * Events for subscriber to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		if ( ! defined( 'ICL_SITEPRESS_VERSION' ) ) {
			return [];
		}

		$events = [
			'rocket_rucss_is_home_url'                => [ 'is_secondary_home', 10, 2 ],
			'rocket_preload_all_to_pending_condition' => 'clean_only_right_domain',
			'rocket_preload_sitemap_before_queue'     => 'add_languages_sitemaps',
			'after_rocket_clean_home'                 => 'remove_root_cached_files',
			'after_rocket_clean_domain'               => 'remove_root_cached_files',
		];

		return $events;
	}

	/**
	 * Checks if page to be processed is secondary homepage.
	 *
	 * @param string $home_url home url.
	 * @param string $url url of current page.
	 * @return string
	 */
	public function is_secondary_home( string $home_url, string $url ): string {
		global $sitepress;

		// Get active languages on site.
		$languages = $sitepress->get_active_languages();

		foreach ( $languages as $lang ) {
			$lang_url = $sitepress->language_url( $lang['code'] );

			// Check if current url is a secondary homepage.
			if ( untrailingslashit( $lang_url ) !== $url ) {
				continue;
			}

			return $url;
		}

		return $home_url;
	}

	/**
	 * Add a condition to clean only urls from the domain when it is the case.
	 *
	 * @param string $condition condition used to clean URLS in the database.
	 * @return string
	 */
	public function clean_only_right_domain( $condition ) {
		global $sitepress;

		$lang = isset( $_GET['lang'] ) && 'all' !== $_GET['lang'] ? sanitize_key( $_GET['lang'] ) : '';// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! $lang ) {
			return $condition;
		}

		$lang_url = $sitepress->language_url( $lang );

		return ' WHERE url LIKE "' . $lang_url . '%"';
	}

	/**
	 * Add sitemaps from translations.
	 *
	 * @param array $sitemaps list of sitemaps to be fetched.
	 * @return array
	 */
	public function add_languages_sitemaps( $sitemaps ) {
			global $sitepress;

			$new_sitemaps = [];

			// Get active languages on site.
			$languages = $sitepress->get_active_languages();

			$base_url = home_url();
		foreach ( $sitemaps as $sitemap ) {
			$new_sitemaps[] = $sitemap;
			foreach ( $languages as $lang ) {
				$lang_url       = $sitepress->language_url( $lang['code'] );
				$new_sitemaps[] = str_replace( $base_url, $lang_url, $sitemap );
			}
		}
		return array_unique( $new_sitemaps );
	}

	/**
	 * Remove root files when WPML is active.
	 *
	 * @return void
	 */
	public function remove_root_cached_files() {
		$site_url               = home_url();
		$host_name              = wp_parse_url( $site_url, PHP_URL_HOST );
		$cache_folder_path      = _rocket_get_wp_rocket_cache_path() . $host_name . '/';
		$cache_folder_directory = $this->filesystem->dirlist( $cache_folder_path );
		foreach ( array_keys( $cache_folder_directory ) as $entry ) {
			if ( $this->filesystem->is_dir( $cache_folder_path . $entry ) ) {
				continue;
			}
			$this->filesystem->delete( $cache_folder_path . $entry );
		}
	}
}
