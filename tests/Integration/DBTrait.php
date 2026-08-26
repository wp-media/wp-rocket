<?php

namespace WP_Rocket\Tests\Integration;

trait DBTrait {

	/**
	 * Container service IDs for every plugin BerlinDB table, in install/uninstall order.
	 *
	 * Single source of truth: install/uninstall/hook-removal all iterate this list, and each
	 * public per-table helper delegates to the generic core using one of these IDs.
	 */
	private static $table_services = [
		'rucss_usedcss_table',
		'preload_caches_table',
		'atf_table',
		'lrc_table',
		'preload_fonts_table',
		'preconnect_external_domains_table',
		'ri_table',
		'rocketcdn_table',
	];

	public static function resourceFound( array $resource ): bool {
		$resource_query = self::container()->get( 'rucss_used_css_query' );
		return count( $resource_query->query( $resource ) ) > 0;
	}

	public static function addResource( array $resource ) {
		$resource_query = self::container()->get( 'rucss_used_css_query' );
		$job_id         = $resource_query->create_new_job( $resource['url'], $resource['job_id'], $resource['queue_name'] );
		if ( key_exists( 'status', $resource ) && 'in-progress' === $resource['status'] ) {
			$resource_query->make_status_inprogress( $resource['url'], $resource['is_mobile'] );
		}
		if ( key_exists( 'status', $resource ) && 'pending' === $resource['status'] ) {
			$resource_query->make_status_pending( $resource['url'], $job_id, $resource['queue_name'], $resource['is_mobile'] );
		}
		if ( key_exists( 'status', $resource ) && 'completed' === $resource['status'] ) {
			$resource_query->make_status_completed( $resource['url'], $resource['is_mobile'], $resource['hash'] );
		}
		return $job_id;
	}

	public static function cacheFound( array $cache ): bool {
		$resource_query = self::container()->get( 'preload_caches_query' );
		return count( $resource_query->query( $cache ) ) > 0;
	}

	public static function addCache( array $resource ) {
		return self::container()->get( 'preload_caches_query' )->create_or_update( $resource );
	}

	public static function addLcp( array $resource ) {
		return self::addItem( 'atf_query', $resource );
	}

	public static function addLrc( array $resource ) {
		return self::addItem( 'lrc_query', $resource );
	}

	public static function addPreloadFonts( array $resource ) {
		return self::addItem( 'preload_fonts_query', $resource );
	}

	public static function addPreconnectExternalDomains( array $resource ) {
		return self::addItem( 'preconnect_external_domains_query', $resource );
	}

	public static function addPerformanceMonitoring( array $resource ) {
		return self::addItem( 'ri_query', $resource );
	}

	public static function installFresh() {
		self::uninstallAll();

		foreach ( self::$table_services as $service ) {
			self::installTable( $service );
		}
	}

	public static function uninstallAll() {
		foreach ( self::$table_services as $service ) {
			self::uninstallTable( $service );
		}
	}

	public static function installUsedCssTable() {
		self::installTable( 'rucss_usedcss_table' );
	}

	public static function installPreconnectExternalDomainsTable() {
		self::installTable( 'preconnect_external_domains_table' );
	}

	public static function installPreloadCacheTable() {
		self::installTable( 'preload_caches_table' );
	}

	public static function installAtfTable() {
		self::installTable( 'atf_table' );
	}

	public static function installLrcTable() {
		self::installTable( 'lrc_table' );
	}

	public static function installPreloadFontsTable() {
		self::installTable( 'preload_fonts_table' );
	}

	public static function installPerformanceMonitoringTable() {
		self::installTable( 'ri_table' );
	}

	public static function installRocketCDNTable() {
		self::installTable( 'rocketcdn_table' );
	}

	public static function uninstallPreconnectDomainsTable() {
		self::uninstallTable( 'preconnect_external_domains_table' );
	}

	public static function uninstallUsedCssTable() {
		self::uninstallTable( 'rucss_usedcss_table' );
	}

	public static function uninstallPreloadCacheTable() {
		self::uninstallTable( 'preload_caches_table' );
	}

	public static function uninstallAtfTable() {
		self::uninstallTable( 'atf_table' );
	}

	public static function uninstallLrcTable() {
		self::uninstallTable( 'lrc_table' );
	}

	public static function uninstallPreloadFontsTable() {
		self::uninstallTable( 'preload_fonts_table' );
	}

	public static function uninstallPerformanceMonitoringTable() {
		self::uninstallTable( 'ri_table' );
	}

	public static function uninstallRocketCDNTable() {
		self::uninstallTable( 'rocketcdn_table' );
	}

	public static function truncateUsedCssTable() {
		self::truncateTable( 'rucss_usedcss_table' );
	}

	public static function truncatePerformanceMonitoringTable() {
		self::truncateTable( 'ri_table' );
	}

	public static function truncateRocketCDNTable() {
		self::truncateTable( 'rocketcdn_table' );
	}

	public static function removeDBHooks() {
		foreach ( self::$table_services as $service ) {
			$table = self::table( $service );
			if ( ! $table ) {
				continue;
			}

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

	/**
	 * The plugin DI container.
	 *
	 * @return mixed
	 */
	private static function container() {
		return apply_filters( 'rocket_container', null );
	}

	/**
	 * Resolve a table instance from the container, or null when the service is not registered.
	 *
	 * Some tables (`ri_table`, `rocketcdn_table`) are not registered in every context, so callers
	 * can iterate {@see self::$table_services} without special-casing them.
	 *
	 * @param string $service Container service ID.
	 *
	 * @return object|null
	 */
	private static function table( string $service ) {
		$container = self::container();

		return $container->has( $service ) ? $container->get( $service ) : null;
	}

	/**
	 * Add an item to a BerlinDB query service.
	 *
	 * @param string $query_service Container service ID of the query.
	 * @param array  $resource      Item data.
	 *
	 * @return mixed
	 */
	private static function addItem( string $query_service, array $resource ) {
		return self::container()->get( $query_service )->add_item( $resource );
	}

	/**
	 * Install a table when it is not already installed, then arm the exists shim.
	 *
	 * Existence is checked through `AbstractTable::exists()` (the cached/transient-backed check),
	 * NOT a raw `SHOW TABLES`. Under the WP test suite `CREATE TABLE` is rewritten to
	 * `CREATE TEMPORARY TABLE`, and `SHOW TABLES` cannot see temporary tables — so a raw check
	 * would report an existing temp table as absent and re-run `CREATE`, triggering a
	 * "table already exists" error. Trusting the cached check avoids that.
	 *
	 * @param string $service Container service ID.
	 *
	 * @return void
	 */
	private static function installTable( string $service ) {
		$table = self::table( $service );

		if ( $table && ! $table->exists() ) {
			$table->install();
		}

		self::add_exists_filter( $table );
	}

	/**
	 * Uninstall a table when it exists, then clear the exists shim.
	 *
	 * @param string $service Container service ID.
	 *
	 * @return void
	 */
	private static function uninstallTable( string $service ) {
		$table = self::table( $service );

		if ( $table && $table->exists() ) {
			$table->uninstall();
		}

		self::remove_exists_filter( $table );
	}

	/**
	 * Truncate a table when it exists.
	 *
	 * @param string $service Container service ID.
	 *
	 * @return void
	 */
	private static function truncateTable( string $service ) {
		$table = self::table( $service );

		if ( $table && $table->exists() ) {
			$table->truncate();
		}
	}

	public static function truncatePreloadCacheTable() {
		$container           = apply_filters( 'rocket_container', null );
		$preload_cache_table = $container->get( 'preload_caches_table' );

		if ( $preload_cache_table && $preload_cache_table->exists() ) {
			$preload_cache_table->truncate();
		}
	}

	private static function add_exists_filter( $table ) {
		if ( ! $table ) {
			return;
		}
		add_filter( 'pre_transient_' . $table->get_exists_transient_name(), '__return_true' );
	}

	private static function remove_exists_filter( $table ) {
		if ( ! $table ) {
			return;
		}
		remove_filter( 'pre_transient_' . $table->get_exists_transient_name(), '__return_true' );
	}
}
