<?php

namespace WP_Rocket\Engine\Debug;

/**
 * Resolver.
 */
class Resolver {

	/**
	 * Array of WP Rocket Options.
	 *
	 * @var array
	 */
	private static $options = [
		'remove_unused_css' => [
			'service' => 'rucss_subscriber',
			'class'   => 'WP_Rocket\Engine\Debug\RUCSS\Subscriber',
		],
	];

	/**
	 * Ships an array of available services.
	 *
	 * @return array Array of services.
	 */
	public static function get_services(): array {
		$set_services = [];

		if ( empty( self::$options ) ) {
			return [];
		}

		foreach ( self::$options as $option => $services ) {

			if ( ! (bool) get_rocket_option( $option ) ) {
				continue;
			}

			$set_services[] = $services;
		}

		return $set_services;
	}
}
