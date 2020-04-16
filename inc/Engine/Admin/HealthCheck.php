<?php

namespace WP_Rocket\Engine\Admin;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

class HealthCheck implements Subscriber_Interface {
	/**
	 * Options_Data instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Constructor
	 *
	 * @param Options_Data $options Options_Data instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'admin_notices' => 'missed_cron',
		];
	}

	/**
	 * Display a warning notice if WP Rocket scheduled events are not running properly
	 *
	 * @since 3.5.4
	 *
	 * @return void
	 */
	public function missed_cron() {
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		if ( 'settings_page_wprocket' !== get_current_screen()->id ) {
			return;
		}

		$dismissed = (array) get_user_meta( get_current_user_id(), 'rocket_boxes', true );

		if ( in_array( 'rocket_warning_cron', $dismissed, true ) ) {
			return;
		}

		if (
				0 === (int) $this->options->get( 'purge_cron_interval', 0 )
				&& 0 === (int) $this->options->get( 'async_css', 0 )
				&& 0 === (int) $this->options->get( 'manual_preload', 0 )
				&& 0 === (int) $this->options->get( 'schedule_automatic_cleanup', 0 )
			) {
			return;
		}

		$events = [
			'rocket_purge_time_event'                      => 'Scheduled Cache Purge',
			'rocket_database_optimization_time_event'      => 'Scheduled Database Optimization',
			'rocket_database_optimization_cron_interval'   => 'Database Optimization Process',
			'rocket_preload_cron_interval'                 => 'Preload',
			'rocket_critical_css_generation_cron_interval' => 'Critical Path CSS Generation Process',
		];

		$delay = rocket_get_constant( 'DISABLE_WP_CRON' ) ? HOUR_IN_SECONDS : 5 * MINUTE_IN_SECONDS;

		foreach ( array_keys( $events ) as $event ) {
			$timestamp = wp_next_scheduled( $event );

			if (
				false === $timestamp
				|| ( $timestamp + $delay ) - time() > 0
			) {
				unset( $events[ $event ] );
				continue;
			}
		}

		if ( empty( $events ) ) {
			return;
		}

		$message = sprintf(
			'<p>%1$s</p>
			<ul>%2$s</ul>
			<p>%3$s</p>',
			_n(
				'The following scheduled event failed to run. This may indicate the CRON system is not running properly, which can prevent some WP Rocket features from working as intended:',
				'The following scheduled events failed to run. This may indicate the CRON system is not running properly, which can prevent some WP Rocket features from working as intended:',
				count( $events ),
				'rocket'
			),
			vsprintf( '<li>%s</li>', $events ),
			__( 'Please contact your host to check if CRON is working.', 'rocket' )
		);

		rocket_notice_html(
			[
				'status'         => 'warning',
				'dismissible'    => '',
				'message'        => $message,
				'dismiss_button' => 'rocket_warning_cron',
			]
		);
	}
}
