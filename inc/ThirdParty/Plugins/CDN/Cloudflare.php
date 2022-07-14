<?php
namespace WP_Rocket\ThirdParty\Plugins\CDN;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Compatibility class for cloudflare.
 *
 * @since 3.11.6
 */
class Cloudflare implements Subscriber_Interface {

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.11.6
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		if ( ! self::is_cloudflare_active() ) {
			return [];
		}

		return [
			'admin_notices' => 'display_server_pushing_mode_notice',
		];
	}

	/**
	 * Check if cloudflare is active.
	 *
	 * @since  3.11.6
	 *
	 * @return boolean
	 */
	protected function is_cloudflare_active() {
		return rocket_is_cloudflare() || defined( 'CLOUDFLARE_PLUGIN_DIR' );
	}

	/**
	 * Display notice for server pushing mode.
	 *
	 * @since  3.11.6
	 *
	 * @return void
	 */
	public function display_server_pushing_mode_notice() {

		if ( ! defined( 'CLOUDFLARE_HTTP2_SERVER_PUSH_ACTIVE' ) ) {
			return;
		}

		$boxes       = get_user_meta( get_current_user_id(), 'rocket_boxes', true );
		$notice_name = 'cloudflare_server_push';

		if ( in_array( $notice_name, (array) $boxes, true ) ) {
			return;
		}

		rocket_notice_html(
			[
				'status'         => 'warning',
				'dismissible'    => '',
				'message'        => __( 'Cloudflare server pushing mode can create incompatiblities with Remove Unused CSS functionality', 'rocket' ),
				'id'             => 'cloudflare_server_push_notice',
				'dismiss_button' => $notice_name,
			]
		);
	}
}
