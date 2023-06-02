<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Plugins\CDN;

use WP_Rocket\Admin\{Options,Options_Data};
use WP_Rocket\Engine\Admin\Beacon\Beacon;
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
	 * Options API instance.
	 *
	 * @var Options
	 */
	private $options_api;

	/**
	 * Beacon
	 *
	 * @var Beacon Beacon instance.
	 */
	private $beacon;

	/**
	 * Constructor.
	 *
	 * @param Options_Data $options Options instance.
	 * @param Options      $options_api Options API instance.
	 * @param Beacon       $beacon Beacon instance.
	 */
	public function __construct( Options_Data $options, Options $options_api, Beacon $beacon ) {
		$this->options     = $options;
		$this->options_api = $options_api;
		$this->beacon      = $beacon;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'admin_notices'                       => [
				[ 'display_server_pushing_mode_notice' ],
				[ 'display_apo_cookies_notice' ],
				[ 'display_apo_cache_notice' ],
			],
			'rocket_display_input_do_cloudflare'  => 'hide_addon_radio',
			'rocket_cloudflare_field_settings'    => 'update_addon_field',
			'pre_get_rocket_option_do_cloudflare' => 'disable_cloudflare_option',
			'cloudflare_purge_everything_actions' => 'add_clean_domain_on_purge',
			'cloudflare_purge_by_url'             => [ 'add_rocket_purge_url_to_purge_url', 10, 2 ],
			'cloudflare_purge_url_actions'        => 'add_after_rocket_clean_to_actions',
			'admin_post_rocket_enable_separate_mobile_cache' => 'enable_separate_mobile_cache',
		];
	}

	/**
	 * Display notice for server pushing mode.
	 *
	 * @return void
	 */
	public function display_server_pushing_mode_notice() {
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
	 * @return bool
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

		$settings['title']       = __( 'Your site is using the official Cloudflare plugin. We have enabled Cloudflare auto-purge for compatibility. If you have APO activated, it is also compatible.', 'rocket' );
		$settings['description'] = __( 'Cloudflare cache will be purged each time WP Rocket clears its cache to ensure content is always up-to-date.', 'rocket' );
		$settings['helper']      = '';

		return $settings;
	}

	/**
	 * Disable WP Rocket CF option when Cloudflare plugin is enabled
	 *
	 * @param mixed $value Pre option value.
	 *
	 * @return bool
	 */
	public function disable_cloudflare_option( $value ) {
		if ( ! $this->is_plugin_active() ) {
			return $value;
		}

		return false;
	}

	/**
	 * Display a notice when APO is enabled and mandatory/dynamic cookies exists
	 *
	 * @return void
	 */
	public function display_apo_cookies_notice() {
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		$screen = get_current_screen();

		if (
			isset( $screen->id )
			&&
			'settings_page_wprocket' !== $screen->id
		) {
			return;
		}

		if (
			empty( get_rocket_cache_mandatory_cookies() )
			&&
			empty( get_rocket_cache_dynamic_cookies() )
		) {
			return;
		}

		if ( ! $this->is_plugin_active() ) {
			return;
		}

		if ( ! $this->is_apo_enabled() ) {
			return;
		}

		$doc = $this->beacon->get_suggest( 'cloudflare_apo' );

		$message = __( 'You are using "Dynamic Cookies Cache". Cloudflare APO is not yet compatible with that feature.', 'rocket' ) . '<br>';

		$message .= sprintf(
			// Translators: %1$s = opening <a> tag, %2$s = closing </a> tag.
			__( 'You should either disable Cloudflare APO or check with the theme/plugin requiring the use of “Dynamic Cookies Cache” developers for an alternative way to be page-cache friendly. %1$sMore info%2$s', 'rocket' ),
			'<a href="' . esc_url( $doc['url'] ) . '" data-beacon-article="' . esc_attr( $doc['id'] ) . '" target="_blank" rel="noopener noreferrer">',
			'</a>'
		);

		rocket_notice_html(
			[
				'status'      => 'warning',
				'dismissible' => '',
				'message'     => $message,
			]
		);
	}

	/**
	 * Display a notice when there is a mismatch between WP Rocket separate cache by mobile value and APO cache by device type
	 *
	 * @return void
	 */
	public function display_apo_cache_notice() {
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		$screen = get_current_screen();

		if (
			isset( $screen->id )
			&&
			'settings_page_wprocket' !== $screen->id
		) {
			return;
		}
		if ( ! $this->is_plugin_active() ) {
			return;
		}

		if ( ! $this->is_apo_enabled() ) {
			return;
		}

		$mobile_cache   = $this->options->get( 'do_caching_mobile_files', 0 );
		$cf_device_type = get_option( 'automatic_platform_optimization_cache_by_device_type', [] );
		if ( (int) $mobile_cache === (int) $cf_device_type['value'] ) {
			return;
		}

		$doc = $this->beacon->get_suggest( 'cloudflare_apo' );

		if (
			1 === (int) $mobile_cache
			&&
			0 === (int) $cf_device_type['value']
		) {
			rocket_notice_html(
				[
					'status'      => 'warning',
					'dismissible' => '',
					'message'     => sprintf(
						// Translators: %1$s = opening <a> tag, %2$s = closing </a> tag.
						__( 'You are using "Separate cache files for mobile devices". You need to activate "Cache by Device Type" on Cloudflare APO to serve the right version of the cache: (add the path to activate "Cache by Device Type" on Cloudflare plugin). %1$sMore info%2$s', 'rocket' ),
						'<a href="' . esc_url( $doc['url'] ) . '" data-beacon-article="' . esc_attr( $doc['id'] ) . '" target="_blank" rel="noopener noreferrer">',
						'</a>'
					),
				]
			);
		} elseif (
			0 === (int) $mobile_cache
			&&
			1 === (int) $cf_device_type['value']
		) {
			rocket_notice_html(
				[
					'status'         => 'warning',
					'message'        => __( 'You have "Cache by Device Type" enabled on Cloudflare APO. If you judge it necessary for the website to have a different cache on mobile and desktop, we suggest you enable our “Separate Cache Files for Mobiles Devices” to ensure the generated cache is accurate.', 'rocket' ),
					'dismiss_button' => true,
					'action'         => 'enable_separate_mobile_cache',
				]
			);
		}
	}

	/**
	 * Adds clear WP Rocket cache on CF purge
	 *
	 * @param array $actions Actions to clear Cloudflare.
	 *
	 * @return array
	 */
	public function add_clean_domain_on_purge( $actions ) {
		$actions[] = 'after_rocket_clean_domain';

		return $actions;
	}

	/**
	 * Add WP Rocket purge URLs list to CF purge URLs list
	 *
	 * @param array $urls CF purge URLs list.
	 * @param int   $post_id Post ID.
	 *
	 * @return array
	 */
	public function add_rocket_purge_url_to_purge_url( $urls, $post_id ) {
		$post = get_post( $post_id );

		if ( empty( $post ) ) {
			return $urls;
		}

		$rocket_urls = rocket_get_purge_urls( $post_id, $post );

		return array_unique( array_merge( $urls, $rocket_urls ) );
	}

	/**
	 * Adds clear WP Rocket partial cache on CF partial purge
	 *
	 * @param array $actions Actions to clear CF URL cache.
	 *
	 * @return array
	 */
	public function add_after_rocket_clean_to_actions( $actions ) {
		$actions[] = 'after_rocket_clean_post';

		return $actions;
	}

	/**
	 * Enable separate cache for mobile option
	 *
	 * @return void
	 */
	public function enable_separate_mobile_cache() {
		check_admin_referer( 'rocket_enable_separate_mobile_cache' );

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		$this->options->set( 'do_caching_mobile_files', 1 );
		$this->options_api->set( 'settings', $this->options->get_options() );
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
			empty( get_option( 'cloudflare_api_email', '' ) )
			||
			empty( get_option( 'cloudflare_api_key', '' ) )
			||
			empty( get_option( 'cloudflare_cached_domain_name', '' ) )
		) {
			return false;
		}

		return true;
	}

	/**
	 * Checks if CF APO is enabled
	 *
	 * @return bool
	 */
	private function is_apo_enabled(): bool {
		$headers = wp_get_http_headers( home_url() );

		return (
			isset( $headers['cf-edge-cache'] )
			&&
			'cache, platform=wordpress' === $headers['cf-edge-cache'] // phpcs:ignore WordPress.WP.CapitalPDangit.Misspelled
		);
	}
}
