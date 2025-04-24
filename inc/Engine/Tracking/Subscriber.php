<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Tracking;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * @var Options_Data
	 */
	private $options;

	/**
	 * @var Tracking
	 */
	private $tracker;

	/**
	 * Subscriber constructor.
	 *
	 * @param Options_Data $options
	 */
	public function __construct( Options_Data $options, Tracking $tracker )
	{
		$this->options = $options;
		$this->tracker = $tracker;
	}

	/**
	 * Returns the list of events to subscribe to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'admin_init' => 'send_analytics_data',
		];
	}

	/**
	 * Send analytics data to Mixpanel.
	 */
	public function send_analytics_data() {
		if ( ! $this->options->get( 'analytics_enabled' ) ) {
			return;
		}

		if( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		if ( false !== get_transient( 'rocket_send_analytics_data' ) ) {
			return;
		}

		$this->tracker->track( 'WP Rocket', rocket_analytics_data() );

		set_transient( 'rocket_send_analytics_data', 1, 7 * DAY_IN_SECONDS );
	}
}
