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
	private $options_services = [
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
	private $options;

	/**
	 * Instantiate the class.
	 *
	 * @param Options_Data $options Options instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * Ships an array of available services.
	 *
	 * @return array Array of services.
	 */
	public function get_services(): array {
		$set_services = [];

		if ( empty( $this->options_services ) ) {
			return [];
		}

		foreach ( $this->options_services as $option => $services ) {
			if ( ! (bool) $this->options->get( $option, 0 ) ) {
				continue;
			}

			$set_services[] = $services;
		}

		return $set_services;
	}
}
