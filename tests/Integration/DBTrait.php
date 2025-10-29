<?php

namespace WP_Rocket\Tests\Integration;

trait DBTrait {
	public static function resourceFound( array $resource ): bool {
		$container = apply_filters( 'rocket_container', null );
		$resource_query = $container->get( 'rucss_used_css_query' );
		return count($resource_query->query( $resource )) > 0;
	}

	public static function addResource(array $resource) {
		$container = apply_filters( 'rocket_container', null );
		$resource_query = $container->get( 'rucss_used_css_query' );
		$job_id = $resource_query->create_new_job($resource['url'], $resource['job_id'], $resource['queue_name']);
		if(key_exists('status', $resource) && 'in-progress' === $resource['status']) {
			$resource_query->make_status_inprogress($resource['url'], $resource['is_mobile']);
		}
		if(key_exists('status', $resource) && 'pending' === $resource['status']) {
			$resource_query->make_status_pending($resource['url'], $job_id, $resource['queue_name'], $resource['is_mobile']);
		}
		if(key_exists('status', $resource) && 'completed' === $resource['status']) {
			$resource_query->make_status_completed($resource['url'], $resource['is_mobile'], $resource['hash']);
		}
		return $job_id;
	}

	public static function cacheFound( array $cache): bool {
		$container = apply_filters( 'rocket_container', null );
		$resource_query = $container->get( 'preload_caches_query' );
		return count($resource_query->query( $cache )) > 0;
	}

	public static function truncateUsedCssTable() {
		$container           = apply_filters( 'rocket_container', null );
		$rucss_usedcss_table = $container->get( 'rucss_usedcss_table' );

		if ( $rucss_usedcss_table->exists() ) {
			$rucss_usedcss_table->truncate();
		}
	}

	public static function addCache( array $resource ) {
		$container = apply_filters( 'rocket_container', null );
		$cache_query = $container->get( 'preload_caches_query' );
		return $cache_query->create_or_update( $resource );
	}

	public static function addLcp( array $resource ) {
		$container = apply_filters( 'rocket_container', null );
		$lcp_query = $container->get( 'atf_query' );
		return $lcp_query->add_item( $resource );
	}

	public static function addLrc( array $resource ) {
		$container = apply_filters( 'rocket_container', null );
		$lrc_query = $container->get( 'lrc_query' );

		return $lrc_query->add_item( $resource );
	}

	public static function addPreloadFonts(array $resource) {
		$container = apply_filters( 'rocket_container', null );
		$preload_fonts_query = $container->get( 'preload_fonts_query' );
		return $preload_fonts_query->add_item( $resource );
	}

	public static function addPreconnectExternalDomains(array $resource) {
		$container = apply_filters( 'rocket_container', null );
		$preconnect_external_domains = $container->get( 'preconnect_external_domains_query' );

		return $preconnect_external_domains->add_item( $resource );
	}

	public static function addPerformanceMonitoring(array $resource) {
		$container = apply_filters( 'rocket_container', null );
		$ri_query = $container->get( 'ri_query' );

		return $ri_query->add_item( $resource );
	}

	public static function installFresh() {
		$container = apply_filters( 'rocket_container', null );

		self::uninstallAll();

		$rucss_usedcss_table = $container->get( 'rucss_usedcss_table' );
		$rucss_usedcss_table->install();
		add_filter( 'pre_transient_' . $rucss_usedcss_table->get_exists_transient_name(), '__return_true' );

		$preload_cache_table = $container->get( 'preload_caches_table' );
		$preload_cache_table->install();
		add_filter( 'pre_transient_' . $preload_cache_table->get_exists_transient_name(), '__return_true' );

		$atf_table = $container->get( 'atf_table' );
		$atf_table->install();
		add_filter( 'pre_transient_' . $atf_table->get_exists_transient_name(), '__return_true' );

		$lrc_table = $container->get( 'lrc_table' );
		$lrc_table->install();
		add_filter( 'pre_transient_' . $lrc_table->get_exists_transient_name(), '__return_true' );

		$preload_fonts_table = $container->get( 'preload_fonts_table' );
		$preload_fonts_table->install();
		add_filter( 'pre_transient_' . $preload_fonts_table->get_exists_transient_name(), '__return_true' );

		$preconnect_external_domains_table = $container->get( 'preconnect_external_domains_table' );
		$preconnect_external_domains_table->install();
		add_filter( 'pre_transient_' . $preconnect_external_domains_table->get_exists_transient_name(), '__return_true' );

		$ri_table = $container->get( 'ri_table' );
		$ri_table->install();
	}

	public static function installUsedCssTable() {
		$container           = apply_filters( 'rocket_container', null );
		$rucss_usedcss_table = $container->get( 'rucss_usedcss_table' );

		if ( ! $rucss_usedcss_table->exists() ) {
			$rucss_usedcss_table->install();
		}

		add_filter( 'pre_transient_' . $rucss_usedcss_table->get_exists_transient_name(), '__return_true' );
	}

	public static function installPreconnectExternalDomainsTable() {
		$container = apply_filters( 'rocket_container', null );
		$preconnect_external_domains_table = $container->get( 'preconnect_external_domains_table' );

		if ( ! $preconnect_external_domains_table->exists() ) {
			$preconnect_external_domains_table->install();
		}

		add_filter( 'pre_transient_' . $preconnect_external_domains_table->get_exists_transient_name(), '__return_true' );
	}

	public static function installPreloadCacheTable() {
		$container           = apply_filters( 'rocket_container', null );
		$preload_cache_table = $container->get( 'preload_caches_table' );

		if ( ! $preload_cache_table->exists() ) {
			$preload_cache_table->install();
		}

		add_filter( 'pre_transient_' . $preload_cache_table->get_exists_transient_name(), '__return_true' );
	}

	public static function installAtfTable() {
		$container = apply_filters( 'rocket_container', null );
		$atf_table = $container->get( 'atf_table' );

		if ( ! $atf_table->exists() ) {
			$atf_table->install();
		}

		add_filter( 'pre_transient_' . $atf_table->get_exists_transient_name(), '__return_true' );
	}

	public static function installLrcTable() {
		$container = apply_filters( 'rocket_container', null );
		$lrc_table = $container->get( 'lrc_table' );

		if ( ! $lrc_table->exists() ) {
			$lrc_table->install();
		}

		add_filter( 'pre_transient_' . $lrc_table->get_exists_transient_name(), '__return_true' );
	}

	public static function installPreloadFontsTable() {
		$container = apply_filters( 'rocket_container', null );
		$preload_fonts_table = $container->get( 'preload_fonts_table' );

		if ( ! $preload_fonts_table->exists() ) {
			$preload_fonts_table->install();
		}

		add_filter( 'pre_transient_' . $preload_fonts_table->get_exists_transient_name(), '__return_true' );
	}

	public static function installPerformanceMonitoringTable() {
		$container = apply_filters( 'rocket_container', null );
		$ri_table = $container->get( 'ri_table' );

		if ( ! $ri_table->exists() ) {
			$ri_table->install();
		}

		add_filter( 'pre_transient_' . $ri_table->get_exists_transient_name(), '__return_true' );
	}

	public static function uninstallAll() {
		$container           = apply_filters( 'rocket_container', null );
		$rucss_usedcss_table = $container->get( 'rucss_usedcss_table' );

		if ( $rucss_usedcss_table->exists() ) {
			$rucss_usedcss_table->uninstall();
		}
		remove_filter( 'pre_transient_' . $rucss_usedcss_table->get_exists_transient_name(), '__return_true' );

		$preload_cache_table = $container->get( 'preload_caches_table' );
		if ( $preload_cache_table->exists() ) {
			$preload_cache_table->uninstall();
		}
		remove_filter( 'pre_transient_' . $preload_cache_table->get_exists_transient_name(), '__return_true' );

		$atf_table = $container->get( 'atf_table' );
		if ( $atf_table->exists() ) {
			$atf_table->uninstall();
		}
		remove_filter( 'pre_transient_' . $atf_table->get_exists_transient_name(), '__return_true' );

		$lrc_table = $container->get( 'lrc_table' );
		if ( $lrc_table->exists() ) {
			$lrc_table->uninstall();
		}
		remove_filter( 'pre_transient_' . $lrc_table->get_exists_transient_name(), '__return_true' );

		$preload_fonts_table = $container->get( 'preload_fonts_table' );
		if ( $preload_fonts_table->exists() ) {
			$preload_fonts_table->uninstall();
		}
		remove_filter( 'pre_transient_' . $preload_fonts_table->get_exists_transient_name(), '__return_true' );

		$preconnect_external_domains_table = $container->get( 'preconnect_external_domains_table' );
		if ( $preconnect_external_domains_table->exists() ) {
			$preconnect_external_domains_table->uninstall();
		}
		remove_filter( 'pre_transient_' . $preconnect_external_domains_table->get_exists_transient_name(), '__return_true' );

		if ( ! $container->has( 'ri_table' ) ) {
			return;
		}
		$ri_table = $container->get( 'ri_table' );
		if ( $ri_table->exists() ) {
			$ri_table->uninstall();
		}
		remove_filter( 'pre_transient_' . $ri_table->get_exists_transient_name(), '__return_true' );
	}

	public static function uninstallPreconnectDomainsTable() {
		$container = apply_filters( 'rocket_container', null );
		$preconnect_external_domains_table = $container->get( 'preconnect_external_domains_table' );

		if ( $preconnect_external_domains_table->exists() ) {
			$preconnect_external_domains_table->uninstall();
		}
		remove_filter( 'pre_transient_' . $preconnect_external_domains_table->get_exists_transient_name(), '__return_true' );
	}

	public static function uninstallUsedCssTable() {
		$container           = apply_filters( 'rocket_container', null );
		$rucss_usedcss_table = $container->get( 'rucss_usedcss_table' );

		if ( $rucss_usedcss_table->exists() ) {
			$rucss_usedcss_table->uninstall();
		}

		remove_filter( 'pre_transient_' . $rucss_usedcss_table->get_exists_transient_name(), '__return_true' );
	}

	public static function uninstallPreloadCacheTable() {
		$container           = apply_filters( 'rocket_container', null );
		$preload_cache_table = $container->get( 'preload_caches_table' );

		if ( $preload_cache_table->exists() ) {
			$preload_cache_table->uninstall();
		}

		remove_filter( 'pre_transient_' . $preload_cache_table->get_exists_transient_name(), '__return_true' );
	}

	public static function uninstallAtfTable() {
		$container = apply_filters( 'rocket_container', null );
		$atf_table = $container->get( 'atf_table' );

		if ( $atf_table->exists() ) {
			$atf_table->uninstall();
		}
		remove_filter( 'pre_transient_' . $atf_table->get_exists_transient_name(), '__return_true' );
	}

	public static function uninstallLrcTable() {
		$container = apply_filters( 'rocket_container', null );
		$lrc_table = $container->get( 'lrc_table' );

		if ( $lrc_table->exists() ) {
			$lrc_table->uninstall();
		}

		remove_filter( 'pre_transient_' . $lrc_table->get_exists_transient_name(), '__return_true' );
	}

	public static function uninstallPreloadFontsTable() {
		$container = apply_filters( 'rocket_container', null );
		$preload_fonts_table = $container->get( 'preload_fonts_table' );

		if ( $preload_fonts_table->exists() ) {
			$preload_fonts_table->uninstall();
		}

		remove_filter( 'pre_transient_' . $preload_fonts_table->get_exists_transient_name(), '__return_true' );
	}

	public static function uninstallPerformanceMonitoringTable() {
		$container = apply_filters( 'rocket_container', null );
		$ri_table = $container->get( 'ri_table' );

		if ( $ri_table->exists() ) {
			$ri_table->uninstall();
		}

		remove_filter( 'pre_transient_' . $ri_table->get_exists_transient_name(), '__return_true' );
	}

	public static function removeDBHooks() {
		$container           = apply_filters( 'rocket_container', null );

		$tables = [
			$container->get( 'rucss_usedcss_table' ),
			$container->get( 'preload_caches_table' ),
			$container->get( 'atf_table' ),
			$container->get( 'lrc_table' ),
			$container->get( 'preload_fonts_table' ),
			$container->get( 'preconnect_external_domains_table' ),
		];
		if ( $container->has( 'ri_table' ) ) {
			$tables[] = $container->get( 'ri_table' );
		}

		foreach ( $tables as $table ) {
			self::forceRemoveTableAdminInitHooks( 'init', get_class( $table ), 'maybe_upgrade', 10 );
			self::forceRemoveTableAdminInitHooks( 'admin_init', get_class( $table ), 'maybe_upgrade', 10 );
			self::forceRemoveTableAdminInitHooks( 'switch_blog', get_class( $table ), 'switch_blog', 10 );
		}
	}

	public static function forceRemoveTableAdminInitHooks( $hook_name = '', $class_name = '', $method_name = '', $priority = 0 ) {
		global $wp_filter;

		// Take only filters on right hook name and priority
		if ( ! isset( $wp_filter[ $hook_name ][ $priority ] ) || ! is_array( $wp_filter[ $hook_name ][ $priority ] ) ) {
			return false;
		}

		// Loop on filters registered
		foreach ( (array) $wp_filter[ $hook_name ][ $priority ] as $unique_id => $filter_array ) {
			// Test if filter is an array ! (always for class/method)
			if ( isset( $filter_array['function'] ) && is_array( $filter_array['function'] ) ) {
				// Test if object is a class, class and method is equal to param !
				if ( is_object( $filter_array['function'][0] ) && get_class( $filter_array['function'][0] ) && get_class( $filter_array['function'][0] ) == $class_name && $filter_array['function'][1] == $method_name ) {
					// Test for WordPress >= 4.7 WP_Hook class (https://make.wordpress.org/core/2016/09/08/wp_hook-next-generation-actions-and-filters/)
					if ( is_a( $wp_filter[ $hook_name ], 'WP_Hook' ) ) {
						unset( $wp_filter[ $hook_name ]->callbacks[ $priority ][ $unique_id ] );
					} else {
						unset( $wp_filter[ $hook_name ][ $priority ][ $unique_id ] );
					}
				}
			}

		}

		return false;
	}

	public static function truncatePerformanceMonitoringTable() {
		$container           = apply_filters( 'rocket_container', null );
		$ri_table = $container->get( 'ri_table' );

		if ( $ri_table->exists() ) {
			$ri_table->truncate();
		}

	}
}
