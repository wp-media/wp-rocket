<?php
namespace WP_Rocket\Engine\Tracking;

trait TrackingTrait {

	/**
	 * Track event with data.
	 *
	 * @param string $event_name Event name.
	 * @param array  $event_data Event data.
	 */
	protected function track_event( $event_name, array $event_data = [] ) {
		/**
		 * Fires when we need to send a new event.
		 *
		 * @param string $event_name Event name.
		 * @param array $event_data Event data.
		 */
		do_action( 'rocket_mixpanel_track_event', $event_name, $event_data );
	}
}
