<?php

namespace WP_Rocket\Tests\Integration;

trait DBTrait {
	public static function resourceFound( array $resource ) : bool {
		$container = apply_filters( 'rocket_container', null );
		$resource_query = $container->get( 'rucss_used_css_query' );
		return count($resource_query->query( $resource )) > 0;
	}

	public static function addResource(array $resource) {
		$container = apply_filters( 'rocket_container', null );
		$resource_query = $container->get( 'rucss_used_css_query' );
		$job_id = $resource_query->create_new_job($resource['url'], $resource['job_id'], $resource['queue_name']);
		if(key_exists('status', $resource) && 'in-progress' === $resource['status']) {
			$resource_query->make_status_inprogress($job_id);
		}
		if(key_exists('status', $resource) && 'pending' === $resource['status']) {
			$resource_query->make_status_pending($job_id);
		}
		if(key_exists('status', $resource) && 'completed' === $resource['status']) {
			$resource_query->make_status_completed($job_id, $resource['hash']);
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

		if ( $rucss_usedcss_table->exists() ){
			$rucss_usedcss_table->truncate();
		}
	}

	public static function addCache( array $resource ) {
		$container = apply_filters( 'rocket_container', null );
		$cache_query = $container->get( 'preload_caches_query' );
		return $cache_query->create_or_update( $resource );
	}

	public static function installFresh() {
		$container             = apply_filters( 'rocket_container', null );

		self::uninstallAll();

		$rucss_usedcss_table   = $container->get( 'rucss_usedcss_table' );
		$rucss_usedcss_table->install();
		$container->get( 'rucss_used_css_query' )::$table_exists = true;
		$preload_cache_table = $container->get( 'preload_caches_table' );
		$preload_cache_table->install();
	}

	public static function uninstallAll() {
		$container           = apply_filters( 'rocket_container', null );
		$rucss_usedcss_table = $container->get( 'rucss_usedcss_table' );

		if ( $rucss_usedcss_table->exists() ) {
			$rucss_usedcss_table->uninstall();
		}

		$preload_cache_table = $container->get( 'preload_caches_table' );
		if ( $preload_cache_table->exists() ) {
			$preload_cache_table->uninstall();
		}
	}

	public static function removeDBHooks() {
		$container             = apply_filters( 'rocket_container', null );
		$rucss_usedcss_table   = $container->get( 'rucss_usedcss_table' );
		$preload_table         = $container->get( 'preload_caches_table' );

		self::forceRemoveTableAdminInitHooks( 'admin_init', get_class( $rucss_usedcss_table ), 'maybe_upgrade', 10);
		self::forceRemoveTableAdminInitHooks( 'switch_blog', get_class( $rucss_usedcss_table ), 'switch_blog', 10);
		self::forceRemoveTableAdminInitHooks( 'admin_init', get_class( $preload_table ), 'maybe_upgrade', 10);
		self::forceRemoveTableAdminInitHooks( 'switch_blog', get_class( $preload_table ), 'switch_blog', 10);
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
}
