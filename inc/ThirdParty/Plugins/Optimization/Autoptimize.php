<?php

declare( strict_types=1 );

namespace WP_Rocket\ThirdParty\Plugins\Optimization;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Autoptimize implements Subscriber_Interface {
	/**
	 * WP Rocket Options instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Array containing the errors
	 *
	 * @var array
	 */
	private $errors = [];

	/**
	 * Constructor
	 *
	 * @param Options_Data $options WP Rocket Options instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @return array
	 * @since  3.10.4
	 */
	public static function get_subscribed_events() {
		return [
			'admin_notices' => [ 'warn_when_js_aggregation_and_delay_js_active' ],
		];
	}

	/**
	 * Add an admin warning notice when Delay JS and JS Aggregation are both activated.
	 *
	 * @since 3.10.4
	 */
	public function warn_when_js_aggregation_and_delay_js_active() {
		if ( ! rocket_get_constant( 'AUTOPTIMIZE_PLUGIN_VERSION', false ) ) {
			return;
		}

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		$autoptimize_aggregate_js_setting = get_option( 'autoptimize_js_aggregate' );
		$boxes                            = get_user_meta( get_current_user_id(), 'rocket_boxes', true );

		if ( 'on' !== $autoptimize_aggregate_js_setting || false === (bool) $this->options->get( 'delay_js' ) ) {
			if ( ! is_array( $boxes ) ) {
				return;
			}

			$this->remove_warning_dismissal( $boxes, __FUNCTION__ );

			return;
		}

		if ( in_array( __FUNCTION__, (array) $boxes, true ) ) {
			return;
		}

		$message = '<strong>' . __(
			'We have detected that Autoptimize\'s JavaScript Aggregation feature is enabled. The Delay JavaScript Execution will not be applied to the file it creates. We suggest disabling it to take full advantage of Delay JavaScript Execution.',
				'rocket'
			) . '</strong>';

		rocket_notice_html(
			[
				'status'         => 'warning',
				'message'        => $message,
				'dismissable'    => '',
				'dismiss_button' => __FUNCTION__,
			]
		);
	}

	/**
	 * Remove a warning box dismissal.
	 *
	 * @param array  $boxes The rocket_boxes user meta.
	 * @param string $name  Slug for the box to be removed.
	 */
	private function remove_warning_dismissal( $boxes, $name ) {
		if ( ! in_array( $name, $boxes, true ) ) {
			return;
		}

		unset( $boxes[ array_search( $name, $boxes, true ) ] );
		update_user_meta( get_current_user_id(), 'rocket_boxes', $boxes );
	}
}
