<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Tracking;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	private $tracking;

	/**
	 * Constructor.
	 *
	 * @param Tracking $tracking The tracking service.
	 */
	public function __construct( Tracking $tracking ) {
		$this->tracking = $tracking;
	}

	/**
	 * Returns an array of events this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'update_option_wp_rocket_settings' => [ 'track_option_change', 10, 2 ],
		];
	}

	/**
	 * Track option change.
	 *
	 * @param mixed $old_value The old value of the option.
	 * @param mixed $value     The new value of the option.
	 *
	 * @return void
	 */
	public function track_option_change( $old_value, $value ): void {
		$this->tracking->track_option_change( $old_value, $value );
	}
}
