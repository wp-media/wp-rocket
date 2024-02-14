<?php

namespace WP_Rocket\Engine\Debug;

use WP_Rocket\Admin\Options_Data;

/**
 * Resolver.
 */
class Resolver {

	/**
	 * Array of WP Rocket Options.
	 *
	 * @var array
	 */
	private static $options_services = [
		'remove_unused_css' => [
			'service' => 'rucss_debug_subscriber',
			'class'   => 'WP_Rocket\Engine\Debug\RUCSS\Subscriber',
		],
	];

	/**
	 * Debug options instance.
	 *
	 * @var Options_Data
	 */
	private static $options;

	/**
	 * Instantiate the class.
	 *
	 * @param Options_Data $options Options instance.
	 */
	public function __construct( Options_Data $options ) {
		self::$options = $options;
	}

	/**
	 * Ships an array of available services.
	 *
	 * @return array Array of services.
	 */
	public static function get_services(): array {
		$set_services = [];

		if ( empty( self::$options_services ) ) {
			return [];
		}

		foreach ( self::$options_services as $option => $services ) {
			if ( ! (bool) self::$options->get( $option, 0 ) ) {
				continue;
			}

			$set_services[] = $services;
		}

		return $set_services;
	}
}
