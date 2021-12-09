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
	 * @since  3.10.4
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'admin_notices' => [
				[ 'warn_when_js_aggregation_and_delay_js_active' ],
				[ 'warn_when_aggregate_inline_css_and_cpcss_active' ],
			],
		];
	}

	/**
	 * Add an admin warning notice when Delay JS and JS Aggregation are both activated.
	 *
	 * @since 3.10.4
	 */
	public function warn_when_js_aggregation_and_delay_js_active() {
		if ( ! $this->can_notify() ) {
			return;
		}

		$boxes = get_user_meta( get_current_user_id(), 'rocket_boxes', true );

		if ( ! (
				'on' === get_option( 'autoptimize_js' )
				&&
				'on' === get_option( 'autoptimize_js_aggregate' )
			) || false === (bool) $this->options->get( 'delay_js' )
		) {
			if ( ! is_array( $boxes ) ) {
				return;
			}

			$this->remove_warning_dismissal( $boxes, __FUNCTION__ );

			return;
		}

		if ( in_array( __FUNCTION__, (array) $boxes, true ) ) {
			return;
		}

		$message = sprintf(
		/* Translators: %1$s is an opening <strong> tag; %2$s is a closing </strong> tag */
			__(
				'%1$sWP Rocket: %2$sWe have detected that Autoptimize\'s JavaScript Aggregation feature is enabled. WP Rocket\'s Delay JavaScript Execution will not be applied to the file it creates. We suggest disabling %1$sJavaScript Aggregation%2$s to take full advantage of Delay JavaScript Execution.',
				'rocket'
			),
			'<strong>',
			'</strong>'
		);

		rocket_notice_html(
			[
				'status'         => 'info',
				'message'        => $message,
				'dismissible'    => '',
				'dismiss_button' => __FUNCTION__,
			]
		);
	}

	/**
	 * Add an admin warning notice when CPCSS and Aggregate Inline CSS are both activated.
	 *
	 * @since 3.10.4
	 */
	public function warn_when_aggregate_inline_css_and_cpcss_active() {
		if ( ! $this->can_notify() ) {
			return;
		}

		$boxes = get_user_meta( get_current_user_id(), 'rocket_boxes', true );

		if ( ! (
			'on' === get_option( 'autoptimize_css' )
			&&
			'on' === get_option( 'autoptimize_css_aggregate' )
			&&
			'on' === get_option( 'autoptimize_css_include_inline' )
			)
			||
			false === (bool) $this->options->get( 'async_css' )
		) {
			if ( ! is_array( $boxes ) ) {
				return;
			}

			$this->remove_warning_dismissal( $boxes, __FUNCTION__ );

			return;
		}

		if ( in_array( __FUNCTION__, (array) $boxes, true ) ) {
			return;
		}

		$message = sprintf(
		/* Translators: %1$s is an opening <strong> tag; %2$s is a closing </strong> tag */
			__(
				'%1$sWP Rocket: %2$sWe have detected that Autoptimize\'s Aggregate Inline CSS feature is enabled. WP Rocket\'s Load CSS Asynchronously will not work correctly. We suggest disabling %1$sAggregate Inline CSS%2$s to take full advantage of Load CSS Asynchronously Execution.',
				'rocket'
			),
			'<strong>',
			'</strong>'
		);

		rocket_notice_html(
			[
				'status'         => 'info',
				'message'        => $message,
				'dismissible'    => '',
				'dismiss_button' => __FUNCTION__,
			]
		);
	}

	/**
	 * Whether this compatibility can use notifications.
	 *
	 * @return bool
	 */
	private function can_notify() {
		if ( 'settings_page_wprocket' !== get_current_screen()->id ) {
			return false;
		}

		return rocket_get_constant( 'AUTOPTIMIZE_PLUGIN_VERSION', false )
			&&
			current_user_can( 'rocket_manage_options' );
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
