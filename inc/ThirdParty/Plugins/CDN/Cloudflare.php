<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Plugins\CDN;

use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Compatibility class for cloudflare.
 */
class Cloudflare implements Subscriber_Interface {
	/**
	 * Options instance.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * @var Options
	 */
	private $option_api;

	/**
	 * Constructor.
	 *
	 * @param Options_Data $options Options instance.
	 * @param Options $option_api
	 */
	public function __construct( Options_Data $options, Options $option_api ) {
		$this->options    = $options;
		$this->option_api = $option_api;
	}


	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'admin_notices' => 'display_server_pushing_mode_notice',
			'rocket_display_input_do_cloudflare' => 'hide_addon_radio',
			'rocket_cloudflare_field_settings' => 'update_addon_field',
			'enable_cloudflare/cloudflare.php' => 'disable_on_official',
			'cloudflare_purge_everything_actions' => 'add_clean_domain_on_purge',
			'cloudflare_purge_by_url' => ['add_rocket_purge_url_to_purge_url', 10, 2],
			'cloudflare_purge_url_actions' => 'add_after_rocket_clean_to_actions',
		];
	}

	/**
	 * Display notice for server pushing mode.
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

	/**
	 * Hide WP Rocket CF Addon activation button if the official CF plugin is enabled
	 *
	 * @param bool $enable True to display, False otherwise.
	 *
	 * @return void
	 */
	public function hide_addon_radio( $enable ) {
		if ( ! $this->is_plugin_active() ) {
			return $enable;
		}

		return false;
	}

	/**
	 * Updates WP Rocket CF Addon field when the official CF plugin is enabled
	 *
	 * @param array $settings Array of values to populate the field.
	 *
	 * @return array
	 */
	public function update_addon_field( $settings ) {
		if ( ! $this->is_plugin_active() ) {
			return $settings;
		}

		$settings['title'] = __( 'Your site is using the official Cloudflare plugin. We have enabled Cloudflare auto-purge for compatibility. If you have APO activated, it is also compatible.', 'rocket' );
		$settings['description'] = __( 'Cloudflare cache will be purged each time WP Rocket clears its cache to ensure content is always up-to-date.', 'rocket' );
		$settings['helper'] = '';

		return $settings;
	}

	public function disable_on_official() {

	}

	public function display_apo_cookies_notice() {

	}

	public function display_apo_cache_notice() {

	}

	public function add_clean_domain_on_purge($actions) {

		return $actions;
	}

	public function add_rocket_purge_url_to_purge_url($urls, $post_id) {

		return $urls;
	}

	public function add_after_rocket_clean_to_actions($actions) {

		return $actions;
	}

	/**
	 * Checks if CF plugin is enabled & credentials saved
	 *
	 * @return bool
	 */
	private function is_plugin_active(): bool {
		if ( ! is_plugin_active( 'cloudflare/cloudflare.php' ) ) {
			return false;
		}

		if (
			empty( get_option( 'cloudflare_api_email' ) )
			||
			empty( get_option( 'cloudflare_api_key' ) )
		) {
			return false;
		}

		return true;
	}
}
