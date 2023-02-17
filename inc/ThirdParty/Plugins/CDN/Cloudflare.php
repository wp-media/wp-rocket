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
		return [
			'admin_notices' => 'display_server_pushing_mode_notice',
		];
	}

	/**
	 * Display notice for server pushing mode.
	 *
	 * @since  3.11.6
	 *
	 * @return void
	 */
	public function display_server_pushing_mode_notice() {

		if ( ! rocket_is_cloudflare() ) {
			return;
		}

		if ( ! rocket_get_constant( 'CLOUDFLARE_PLUGIN_DIR' ) ) {
			return;
		}

		if ( ! rocket_get_constant( 'CLOUDFLARE_HTTP2_SERVER_PUSH_ACTIVE' ) ) {
			return;
		}

		$screen = get_current_screen();

		// If current screen is wprocket settings.
		if (
			isset( $screen->id )
			&&
			'settings_page_wprocket' !== $screen->id
		) {
			return;
		}

		// if current user has required capapabilities.
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		// If RUCSS is enabled.
		if ( ! (bool) $this->options->get( 'remove_unused_css', 0 ) && ! (bool) $this->options->get( 'minify_concatenate_css', 0 ) ) {
			return;
		}

		$boxes       = get_user_meta( get_current_user_id(), 'rocket_boxes', true );
		$notice_name = 'cloudflare_server_push';

		if ( in_array( $notice_name, (array) $boxes, true ) ) {
			return;
		}

		$message = sprintf(
			// translators: %1$s = plugin name.
			__( '%1$s: Cloudflare\'s HTTP/2 Server Push is incompatible with the features of Remove Unused CSS and Combine CSS files. We strongly recommend disabling it.', 'rocket' ),
			'<strong>WP Rocket</strong>'
		);

		rocket_notice_html(
			[
				'status'               => 'warning',
				'dismissible'          => '',
				'message'              => $message,
				'id'                   => 'cloudflare_server_push_notice',
				'dismiss_button'       => $notice_name,
				'dismiss_button_class' => 'button-primary',
			]
		);
	}
}
