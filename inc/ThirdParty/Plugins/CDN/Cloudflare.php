<?php
namespace WP_Rocket\ThirdParty\Plugins\CDN;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Admin\Options_Data;

/**
 * Compatibility class for cloudflare.
 *
 * @since 3.11.6
 */
class Cloudflare implements Subscriber_Interface {

	/**
	 * Options instance.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Call class instance.
	 *
	 * @param Options_Data $options Options instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

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
	protected static function is_cloudflare_active() {
		return rocket_is_cloudflare() || defined( 'CLOUDFLARE_PLUGIN_DIR' );
	}

	/**
	 * Should display pushing mode if RUCSS or Combine CSS is enabled.
	 *
	 * @return boolean
	 */
	private function should_display_pushing_mode_notice() {
		$screen = get_current_screen();

		if (
			isset( $screen->id )
			&&
			'settings_page_wprocket' !== $screen->id
		) {
			return false;
		}

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return false;
		}

		// If RUCSS is enabled.
		if ( (bool) $this->options->get( 'remove_unused_css', 0 )
			&&
			defined( 'CLOUDFLARE_HTTP2_SERVER_PUSH_ACTIVE' )
			&&
			CLOUDFLARE_HTTP2_SERVER_PUSH_ACTIVE
		) {
			return true;
		}

		// If Combine CSS is enabled.
		if ( (bool) $this->options->get( 'minify_css', 0 )
			&&
			(bool) $this->options->get( 'minify_concatenate_css', 0 )
			&&
			defined( 'CLOUDFLARE_HTTP2_SERVER_PUSH_ACTIVE' )
			&&
			CLOUDFLARE_HTTP2_SERVER_PUSH_ACTIVE
		) {
			return true;
		}

		return false;
	}

	/**
	 * Display notice for server pushing mode.
	 *
	 * @since  3.11.6
	 *
	 * @return void
	 */
	public function display_server_pushing_mode_notice() {

		if ( ! $this->should_display_pushing_mode_notice() ) {
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
				'message'        => __( 'Cloudflare server pushing mode can create incompatiblities with Remove Unused CSS and Combine CSS features', 'rocket' ),
				'id'             => 'cloudflare_server_push_notice',
				'dismiss_button' => $notice_name,
			]
		);
	}
}
