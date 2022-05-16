<?php

declare( strict_types=1 );

namespace WP_Rocket\Engine\Optimization\RUCSS\Admin\Shutdown;

use WP_Rocket\Event_Management\Subscriber_Interface;
use Exception;

class Subscriber implements Subscriber_Interface {
	/**
	 * RUCSS Shutdown banner render object.
	 *
	 * @var Shutdown
	 */
	private $shutdown;

	/**
	 * Instantiate the class
	 *
	 * @param Shutdown $shutdown RUCSS Shutdown instance.
	 */
	public function __construct( Shutdown $shutdown ) {
		$this->shutdown           = $shutdown;
	}

	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'rocket_before_dashboard_content'     => 'display_before_shutdown_rucss_banner',
			'get_rocket_option_remove_unused_css' => 'disable_rucss_with_shutdown_date',
			'rocket_rucss_shutdown_details'       => 'get_shutdown_details',
		];
	}

	/**
	 * Display RUCSS shutdown warning banner.
	 *
	 * @return void
	 */
	public function display_before_shutdown_rucss_banner() {
		try {
			$this->shutdown->display_shutdown_banner();
		} catch ( Exception $e ) {
			// Do nothing, Don't show the banner.
			return;
		}
	}

	public function disable_rucss_with_shutdown_date( $enabled ) {
		try {
			return $enabled && ! $this->shutdown->is_expired();
		} catch ( Exception $e ) {
			// Do nothing, Don't show the banner.
			return $enabled;
		}
	}

	public function get_shutdown_details( $details ) {
		try {
			$default = [
				'status'      => 0,
				'renewal_url' => '',
			];

			return wp_parse_args( $this->shutdown->get_shutdown_details( $details ), $default );
		} catch ( Exception $e ) {
			// Do nothing, Don't change anything.
			return $details;
		}
	}
}
