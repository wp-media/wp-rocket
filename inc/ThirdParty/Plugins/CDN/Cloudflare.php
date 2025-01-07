<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Plugins\CDN;

use WP_Rocket\Admin\{Options, Options_Data};
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Deactivation\DeactivationInterface;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Compatibility class for cloudflare.
 */
class Cloudflare implements Subscriber_Interface, DeactivationInterface {
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
	 * CloudflareFacade instance.
	 *
	 * @var CloudflareFacade
	 */
	private $facade;

	/**
	 * Constructor.
	 *
	 * @param Options_Data     $options Options instance.
	 * @param Options          $options_api Options API instance.
	 * @param Beacon           $beacon Beacon instance.
	 * @param CloudflareFacade $facade CloudflareFacade instance.
	 */
	public function __construct( Options_Data $options, Options $options_api, Beacon $beacon, CloudflareFacade $facade ) {
		$this->options     = $options;
		$this->options_api = $options_api;
		$this->beacon      = $beacon;
		$this->facade      = $facade;
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
			'rocket_after_clean_domain'           => 'purge_cloudflare',
			'after_rocket_clean_files'            => 'purge_cloudflare_partial',
			'rocket_saas_complete_job_status'     => 'purge_cloudflare_after_usedcss',
			'rocket_rucss_after_clearing_usedcss' => 'purge_cloudflare_after_usedcss',
			'admin_post_rocket_enable_separate_mobile_cache' => 'enable_separate_mobile_cache',
			'rocket_cdn_helper_addons'            => 'add_cdn_helper_message',
			'init'                                => 'unregister_cloudflare_clean_on_post',
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
		if ( ! (bool) $this->options->get( 'remove_unused_css', 0 ) ) {
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

		$settings['do_cloudflare']['title']         = __( 'Your site is using the official Cloudflare plugin. We have enabled Cloudflare auto-purge for compatibility. If you have APO activated, it is also compatible.', 'rocket' );
		$settings['do_cloudflare']['description']   = __( 'Cloudflare cache will be purged each time WP Rocket clears its cache to ensure content is always up-to-date.', 'rocket' );
		$settings['do_cloudflare']['helper']        = '';
		$settings['do_cloudflare']['settings_page'] = '';

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
		if ( ! $this->can_display_notice() ) {
			return;
		}

		if (
			empty( get_rocket_cache_mandatory_cookies() )
			&&
			empty( get_rocket_cache_dynamic_cookies() )
		) {
			return;
		}

		$doc = $this->beacon->get_suggest( 'cloudflare_apo' );

		$message = sprintf(
		// Translators: %1$s = strong opening tag, %2$s = strong closing tag.
		__( '%1$sWP Rocket:%2$s You are using "Dynamic Cookies Cache". Cloudflare APO is not yet compatible with that feature.', 'rocket' ) . '<br>',
		'<strong>',
			'</strong>'
		);
		$message .= sprintf(
			// Translators:%1$s = opening <a> tag, %2$s = closing </a> tag.
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
		if ( ! $this->can_display_notice() ) {
			return;
		}

		$cf_device_type = get_option( 'automatic_platform_optimization_cache_by_device_type', [] );

		if ( ! key_exists( 'value', $cf_device_type ) ) {
			return;
		}

		$mobile_cache = $this->options->get( 'do_caching_mobile_files', 0 );

		if ( (int) $mobile_cache === (int) $cf_device_type['value'] ) {
			return;
		}

		$doc = $this->beacon->get_suggest( 'cloudflare_apo' );

		$boxes = get_user_meta( get_current_user_id(), 'rocket_boxes', true );

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
						// Translators: %1$s = strong opening tag, %2$s = strong closing tag, %3$s = opening <a> tag, %4$s = closing </a> tag, %5$s = opening <a> tag.
						__( '%1$sWP Rocket:%2$s You are using "Separate cache files for mobile devices". You need to activate "Cache by Device Type" %3$ssetting%5$s on Cloudflare APO to serve the right version of the cache. %4$sMore info%5$s', 'rocket' ),
						'<strong>',
						'</strong>',
						'<a href="' . esc_url( admin_url( 'options-general.php?page=cloudflare' ) ) . '">',
						'<a href="' . esc_url( $doc['url'] ) . '" data-beacon-article="' . esc_attr( $doc['id'] ) . '" target="_blank" rel="noopener noreferrer">',
						'</a>'
					),
				]
			);
		} elseif (
			0 === (int) $mobile_cache
			&&
			1 === (int) $cf_device_type['value']
			&&
			! in_array( __FUNCTION__, (array) $boxes, true )
		) {
			rocket_notice_html(
				[
					'status'         => 'warning',
					'message'        => sprintf(
					// Translators: %1$s = strong opening tag, %2$s = strong closing tag.
						__( '%1$sWP Rocket:%2$s You have "Cache by Device Type" enabled on Cloudflare APO. If you judge it necessary for the website to have a different cache on mobile and desktop, we suggest you enable our “Separate Cache Files for Mobiles Devices” to ensure the generated cache is accurate.', 'rocket' ),
						'<strong>',
						'</strong>'
					),
					'dismiss_button' => __FUNCTION__,
					'dismissible'    => '',
					'action'         => 'enable_separate_mobile_cache',
				]
			);
		}
	}

	/**
	 * Checks if APO notices should be displayed
	 *
	 * @return bool
	 */
	private function can_display_notice(): bool {
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return false;
		}

		$screen = get_current_screen();

		if (
			isset( $screen->id )
			&&
			'settings_page_wprocket' !== $screen->id
		) {
			return false;
		}
		if ( ! $this->is_plugin_active() ) {
			return false;
		}

		return $this->is_apo_enabled();
	}

	/**
	 * Purge everything on Cloudflare
	 *
	 * @return void
	 */
	public function purge_cloudflare() {
		if ( ! $this->is_plugin_active() ) {
			return;
		}

		$this->facade->purge_everything();
	}

	/**
	 * Purges posts when using purge this URL button
	 *
	 * @param array $urls Array of URLs.
	 *
	 * @return void
	 */
	public function purge_cloudflare_partial( $urls ) {
		if ( ! $this->is_plugin_active() ) {
			return;
		}

		$post_ids = array_filter( array_map( 'url_to_postid', $urls ) );

		$this->facade->purge_urls( $post_ids );
	}

	/**
	 * Purges CF after Used CSS generation or clean
	 *
	 * @param string $url URL to purge.
	 *
	 * @return void
	 */
	public function purge_cloudflare_after_usedcss( $url ) {
		if ( ! $this->is_plugin_active() ) {
			return;
		}

		$post_id = url_to_postid( $url );

		if ( empty( $post_id ) ) {
			return;
		}

		$this->facade->purge_urls( $post_id );
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

		$this->options->set( 'cache_mobile', 1 );
		$this->options->set( 'do_caching_mobile_files', 1 );
		$this->options_api->set( 'settings', $this->options->get_options() );

		wp_safe_redirect( wp_get_referer() );
		rocket_get_constant( 'WP_ROCKET_IS_TESTING', false ) ? wp_die() : exit;
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
		$is_apo_enabled = get_option( 'automatic_platform_optimization', [] );

		if ( ! key_exists( 'value', $is_apo_enabled ) ) {
			return false;
		}

		return (bool) $is_apo_enabled['value'];
	}

	/**
	 * Add the helper message on the CDN settings.
	 *
	 * @param string[] $addons Name from the addon that requires the helper message.
	 * @return string[]
	 */
	public function add_cdn_helper_message( array $addons ): array {
		if ( ! $this->is_plugin_active() ) {
			return $addons;
		}
		$addons[] = 'Cloudflare';
		return $addons;
	}

	/**
	 * Purge Cloudflare on deactivate.
	 *
	 * @return void
	 */
	public function deactivate() {
		$this->purge_cloudflare();
	}

	/**
	 * Unregister Call on clean posts.
	 *
	 * @return void
	 */
	public function unregister_cloudflare_clean_on_post() {
		$this->unregister_callback( 'deleted_post', 'purgeCacheByRelevantURLs' );
		$this->unregister_callback( 'transition_post_status', 'purgeCacheOnPostStatusChange', PHP_INT_MAX );
	}

	/**
	 * Unregister a callback.
	 *
	 * @param string $hook Hook on which to unregister.
	 * @param string $method The callback to unregister.
	 * @param int    $priority the priority from the callback.
	 * @return void
	 */
	protected function unregister_callback( string $hook, string $method, int $priority = 10 ) {
		global $wp_filter;

		if ( ! key_exists( $hook, $wp_filter ) ) {
			return;
		}

		$original_wp_filter = $wp_filter[ $hook ]->callbacks;

		if ( ! key_exists( $priority, $original_wp_filter ) ) {
			return;
		}

		foreach ( $original_wp_filter[ $priority ] as $key => $config ) {

			if ( substr( $key, - strlen( $method ) ) !== $method ) {
				continue;
			}

			unset( $wp_filter[ $hook ]->callbacks[ $priority ][ $key ] );
		}
	}
}
