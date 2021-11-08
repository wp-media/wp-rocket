<?php

namespace WP_Rocket\Tests\Integration;

trait DBTrait {
	public static function resourceFound( array $resource ) : bool {
		$container = apply_filters( 'rocket_container', null );
		$resource_query = $container->get( 'rucss_resources_query' );
		return $resource_query->query( $resource );
	}

	public static function truncateUsedCssTable() {
		$container             = apply_filters( 'rocket_container', null );
		$rucss_usedcss_table = $container->get( 'rucss_usedcss_table' );
		if ( $rucss_usedcss_table->exists() ){
			$rucss_usedcss_table->truncate();
		}
	}

	public static function installFresh() {
		$container             = apply_filters( 'rocket_container', null );

		self::uninstallAll();

		$rucss_resources_table = $container->get( 'rucss_resources_table' );
		$rucss_resources_table->install();

		$rucss_usedcss_table   = $container->get( 'rucss_usedcss_table' );
		$rucss_usedcss_table->install();
	}

	public static function uninstallAll() {
		$container             = apply_filters( 'rocket_container', null );

		$rucss_resources_table = $container->get( 'rucss_resources_table' );
		if ( $rucss_resources_table->exists() ) {
			$rucss_resources_table->uninstall();
		}

		$rucss_usedcss_table   = $container->get( 'rucss_usedcss_table' );
		if ( $rucss_usedcss_table->exists() ) {
			$rucss_usedcss_table->uninstall();
		}
	}

	public static function removeDBHooks() {
		$container             = apply_filters( 'rocket_container', null );
		$rucss_resources_table = $container->get( 'rucss_resources_table' );
		$rucss_usedcss_table   = $container->get( 'rucss_usedcss_table' );

		self::forceRemoveTableAdminInitHooks( 'admin_init', get_class( $rucss_resources_table ), 'maybe_upgrade', 10);
		self::forceRemoveTableAdminInitHooks( 'switch_blog', get_class( $rucss_resources_table ), 'switch_blog', 10);
		self::forceRemoveTableAdminInitHooks( 'admin_init', get_class( $rucss_usedcss_table ), 'maybe_upgrade', 10);
		self::forceRemoveTableAdminInitHooks( 'switch_blog', get_class( $rucss_usedcss_table ), 'switch_blog', 10);
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
