<?php

namespace WPMedia\Cloudflare;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Options;

/**
 * Cloudflare Subscriber.
 *
 * @since  1.0
 */
class Subscriber implements Subscriber_Interface {

	/**
	 * Cloudflare instance.
	 *
	 * @var Cloudflare
	 */
	private $cloudflare;

	/**
	 * Options Data instance.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Options instance.
	 *
	 * @var Options
	 */
	private $options_api;

	/**
	 * Creates an instance of the Cloudflare Subscriber.
	 *
	 * @param Cloudflare   $cloudflare  Cloudflare instance.
	 * @param Options_Data $options     WP Rocket options instance.
	 * @param Options      $options_api Options instance.
	 */
	public function __construct( Cloudflare $cloudflare, Options_Data $options, Options $options_api ) {
		$this->options     = $options;
		$this->options_api = $options_api;
		$this->cloudflare  = $cloudflare;
	}

	/**
	 * Gets the subscribed events.
	 *
	 * @since 1.0
	 *
	 * @return array subscribed events => callbacks.
	 */
	public static function get_subscribed_events() {
		$slug = rocket_get_constant( 'WP_ROCKET_SLUG', 'wp_rocket_settings' );

		return [
			'rocket_varnish_ip'                         => 'set_varnish_localhost',
			'rocket_varnish_purge_request_host'         => 'set_varnish_purge_request_host',
			'rocket_cron_deactivate_cloudflare_devmode' => 'deactivate_devmode',
			'after_rocket_clean_domain'                 => 'auto_purge',
			'after_rocket_clean_post'                   => [ 'auto_purge_by_url', 10, 3 ],
			'admin_post_rocket_purge_cloudflare'        => 'purge_cache',
			'init'                                      => [ 'set_real_ip', 1 ],
			'update_option_' . $slug                    => [ 'save_cloudflare_options', 10, 2 ],
			'pre_update_option_' . $slug                => [ 'save_cloudflare_old_settings', 10, 2 ],
			'admin_notices'                             => [
				[ 'maybe_display_purge_notice' ],
				[ 'maybe_print_update_settings_notice' ],
			],
		];
	}

	/**
	 * Sets the Varnish IP to localhost if Cloudflare is active.
	 *
	 * @since 1.0
	 *
	 * @param string|array $varnish_ip Varnish IP.
	 *
	 * @return array
	 */
	public function set_varnish_localhost( $varnish_ip ) {
		if ( ! $this->should_filter_varnish() ) {
			return $varnish_ip;
		}

		if ( is_string( $varnish_ip ) ) {
			$varnish_ip = (array) $varnish_ip;
		}

		$varnish_ip[] = 'localhost';

		return $varnish_ip;
	}

	/**
	 * Sets the Host header to the website domain if Cloudflare is active.
	 *
	 * @since 1.0
	 *
	 * @param string $host the host header value.
	 *
	 * @return string
	 */
	public function set_varnish_purge_request_host( $host ) {
		if ( ! $this->should_filter_varnish() ) {
			return $host;
		}

		return wp_parse_url( home_url(), PHP_URL_HOST );
	}

	/**
	 * Checks if we should filter the value for the Varnish purge.
	 *
	 * @since 1.0
	 *
	 * @return bool
	 */
	private function should_filter_varnish() {
		// This filter is documented in inc/classes/subscriber/Addons/Varnish/VarnishSubscriber.php.
		if ( ! apply_filters( 'do_rocket_varnish_http_purge', false ) && ! $this->options->get( 'varnish_auto_purge', 0 ) ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
			return false;
		}

		return true;
	}


	/**
	 * Automatically set Cloudflare development mode value to off after 3 hours to reflect Cloudflare behaviour.
	 *
	 * @since 1.0
	 */
	public function deactivate_devmode() {
		$this->options->set( 'cloudflare_devmode', 'off' );
		$this->options_api->set( 'settings', $this->options->get_options() );
	}

	/**
	 * Purge Cloudflare cache automatically if Cache Everything is set as a Page Rule.
	 *
	 * @since 1.0
	 */
	public function auto_purge() {
		if ( ! current_user_can( 'rocket_purge_cloudflare_cache' ) ) {
			return;
		}

		$cf_cache_everything = $this->cloudflare->has_page_rule( 'cache_everything' );

		if ( is_wp_error( $cf_cache_everything ) || ! $cf_cache_everything ) {
			return;
		}

		// Purge CloudFlare.
		$this->cloudflare->purge_cloudflare();
	}

	/**
	 * Purge Cloudflare cache URLs automatically if Cache Everything is set as a Page Rule.
	 *
	 * @since 1.0
	 *
	 * @param WP_Post $post       The post object.
	 * @param array   $purge_urls URLs cache files to remove.
	 * @param string  $lang       The post language.
	 */
	public function auto_purge_by_url( $post, $purge_urls, $lang ) {
		if ( ! current_user_can( 'rocket_purge_cloudflare_cache' ) ) {
			return;
		}

		$cf_cache_everything = $this->cloudflare->has_page_rule( 'cache_everything' );

		if ( is_wp_error( $cf_cache_everything ) || ! $cf_cache_everything ) {
			return;
		}

		// Add home URL and feeds URLs to Cloudflare clean cache URLs list.
		$purge_urls[] = get_rocket_i18n_home_url( $lang );
		$feed_urls    = [];
		$feed_urls[]  = get_feed_link();
		$feed_urls[]  = get_feed_link( 'comments_' );

		// this filter is documented in inc/functions/files.php.
		$feed_urls  = apply_filters( 'rocket_clean_home_feeds', $feed_urls );
		$purge_urls = array_unique( array_merge( $purge_urls, $feed_urls ) );

		// Purge CloudFlare.
		$this->cloudflare->purge_by_url( $post, $purge_urls, $lang );
	}

	/**
	 * Purge CloudFlare cache.
	 *
	 * @since 1.0
	 */
	public function purge_cache_no_die() {
		if ( ! current_user_can( 'rocket_purge_cloudflare_cache' ) ) {
			return;
		}

		// Purge CloudFlare.
		$cf_purge = $this->cloudflare->purge_cloudflare();

		if ( is_wp_error( $cf_purge ) ) {
			$cf_purge_result = [
				'result'  => 'error',
				// translators: %s = CloudFare API return message.
				'message' => sprintf( __( '<strong>WP Rocket:</strong> %s', 'rocket' ), $cf_purge->get_error_message() ),
			];
		} else {
			$cf_purge_result = [
				'result'  => 'success',
				'message' => __( '<strong>WP Rocket:</strong> Cloudflare cache successfully purged.', 'rocket' ),
			];
		}

		set_transient( get_current_user_id() . '_cloudflare_purge_result', $cf_purge_result );
	}

	/**
	 * Purge CloudFlare cache.
	 *
	 * @since 1.0
	 */
	public function purge_cache() {
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'rocket_purge_cloudflare' ) ) {
			wp_nonce_ays( '' );
		}

		$this->purge_cache_no_die();

		wp_safe_redirect( esc_url_raw( wp_get_referer() ) );
		defined( 'WPMEDIA_IS_TESTING' ) ? wp_die() : exit;
	}

	/**
	 * Set Real IP from CloudFlare.
	 *
	 * @since  1.0
	 * @source cloudflare.php - https://wordpress.org/plugins/cloudflare/
	 */
	public function set_real_ip() {
		// only run this logic if the REMOTE_ADDR is populated, to avoid causing notices in CLI mode.
		if ( ! isset( $_SERVER['HTTP_CF_CONNECTING_IP'], $_SERVER['REMOTE_ADDR'] ) ) {
			return;
		}

		$cf_ips_values = $this->cloudflare->get_cloudflare_ips();
		$cf_ip_ranges  = $cf_ips_values->result->ipv6_cidrs;
		$ip            = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		$ipv6          = get_rocket_ipv6_full( $ip );
		if ( false === strpos( $ip, ':' ) ) {
			// IPV4: Update the REMOTE_ADDR value if the current REMOTE_ADDR value is in the specified range.
			$cf_ip_ranges = $cf_ips_values->result->ipv4_cidrs;
		}

		foreach ( $cf_ip_ranges as $range ) {
			if (
				( strpos( $ip, ':' ) && rocket_ipv6_in_range( $ipv6, $range ) )
				||
				( false === strpos( $ip, ':' ) && rocket_ipv4_in_range( $ip, $range ) )
			) {
				$_SERVER['REMOTE_ADDR'] = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) );
				break;
			}
		}
	}

	/**
	 * This notice is displayed after purging the CloudFlare cache.
	 *
	 * @since 1.0
	 */
	public function maybe_display_purge_notice() {
		if ( ! current_user_can( 'rocket_purge_cloudflare_cache' ) ) {
			return;
		}

		$user_id = get_current_user_id();
		$notice  = get_transient( $user_id . '_cloudflare_purge_result' );
		if ( ! $notice ) {
			return;
		}

		delete_transient( $user_id . '_cloudflare_purge_result' );

		rocket_notice_html(
			[
				'status'  => $notice['result'],
				'message' => $notice['message'],
			]
		);
	}

	/**
	 * This notice is displayed after modifying the CloudFlare settings.
	 *
	 * @since 1.0
	 */
	public function maybe_print_update_settings_notice() {
		$screen = get_current_screen();

		if ( ! current_user_can( 'rocket_manage_options' ) || 'settings_page_wprocket' !== $screen->id ) {
			return;
		}

		$user_id = get_current_user_id();
		$notices = get_transient( $user_id . '_cloudflare_update_settings' );
		if ( ! $notices ) {
			return;
		}

		$errors  = '';
		$success = '';
		delete_transient( $user_id . '_cloudflare_update_settings' );
		foreach ( $notices as $notice ) {
			if ( 'error' === $notice['result'] ) {
				$errors .= $notice['message'] . '<br>';
			} elseif ( 'success' === $notice['result'] ) {
				$success .= $notice['message'] . '<br>';
			}
		}

		if ( ! empty( $success ) ) {
			rocket_notice_html(
				[
					'message' => $success,
				]
			);
		}

		if ( ! empty( $errors ) ) {
			rocket_notice_html(
				[
					'status'  => 'error',
					'message' => $errors,
				]
			);
		}

	}

	/**
	 * Save Cloudflare admin options.
	 *
	 * @since 1.0
	 *
	 * @param array $old_value An array of previous values for the settings.
	 * @param array $value     An array of submitted values for the settings.
	 */
	public function save_cloudflare_options( $old_value, $value ) {
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		if (
			( isset( $old_value['cloudflare_email'], $value['cloudflare_email'] ) && $old_value['cloudflare_email'] !== $value['cloudflare_email'] )
			||
			( isset( $old_value['cloudflare_api_key'], $value['cloudflare_api_key'] ) && $old_value['cloudflare_api_key'] !== $value['cloudflare_api_key'] )
			||
			( isset( $old_value['cloudflare_zone_id'], $value['cloudflare_zone_id'] ) && $old_value['cloudflare_zone_id'] !== $value['cloudflare_zone_id'] )
		) {
			delete_transient( 'rocket_cloudflare_is_api_keys_valid' );
			$is_api_keys_valid_cloudflare = $this->cloudflare->is_api_keys_valid( $value['cloudflare_email'], $value['cloudflare_api_key'], $value['cloudflare_zone_id'], true );
			set_transient( 'rocket_cloudflare_is_api_keys_valid', $is_api_keys_valid_cloudflare, 2 * WEEK_IN_SECONDS );
			if ( is_wp_error( $is_api_keys_valid_cloudflare ) ) {
				$cloudflare_error_message = $is_api_keys_valid_cloudflare->get_error_message();
				add_settings_error( 'general', 'cloudflare_api_key_invalid', __( 'WP Rocket: ', 'rocket' ) . '</strong>' . $cloudflare_error_message . '<strong>', 'error' );
			}
		}

		// Update CloudFlare Development Mode.
		$cloudflare_update_result = [];

		if ( isset( $old_value['cloudflare_devmode'], $value['cloudflare_devmode'] ) && (int) $old_value['cloudflare_devmode'] !== (int) $value['cloudflare_devmode'] ) {
			$cloudflare_dev_mode_return = $this->cloudflare->set_devmode( $value['cloudflare_devmode'] );
			if ( is_wp_error( $cloudflare_dev_mode_return ) ) {
				$cloudflare_update_result[] = [
					'result'  => 'error',
					// translators: %s is the message returned by the CloudFlare API.
					'message' => '<strong>' . __( 'WP Rocket: ', 'rocket' ) . '</strong>' . sprintf( __( 'Cloudflare development mode error: %s', 'rocket' ), $cloudflare_dev_mode_return->get_error_message() ),
				];
			} else {
				$cloudflare_update_result[] = [
					'result'  => 'success',
					// translators: %s is the message returned by the CloudFlare API.
					'message' => '<strong>' . __( 'WP Rocket: ', 'rocket' ) . '</strong>' . sprintf( __( 'Cloudflare development mode %s', 'rocket' ), $cloudflare_dev_mode_return ),
				];
			}
		}

		// Update CloudFlare settings.
		if ( isset( $old_value['cloudflare_auto_settings'], $value['cloudflare_auto_settings'] ) && (int) $old_value['cloudflare_auto_settings'] !== (int) $value['cloudflare_auto_settings'] ) {
			$cf_old_settings = explode( ',', $value['cloudflare_old_settings'] );

			// Set Cache Level to Aggressive.
			$cf_cache_level        = isset( $cf_old_settings[0] ) && 0 === $value['cloudflare_auto_settings'] ? 'basic' : 'aggressive';
			$cf_cache_level_return = $this->cloudflare->set_cache_level( $cf_cache_level );

			if ( is_wp_error( $cf_cache_level_return ) ) {
				$cloudflare_update_result[] = [
					'result'  => 'error',
					// translators: %s is the message returned by the CloudFlare API.
					'message' => '<strong>' . __( 'WP Rocket: ', 'rocket' ) . '</strong>' . sprintf( __( 'Cloudflare cache level error: %s', 'rocket' ), $cf_cache_level_return->get_error_message() ),
				];
			} else {
				if ( 'aggressive' === $cf_cache_level_return ) {
					$cf_cache_level_return = _x( 'Standard', 'Cloudflare caching level', 'rocket' );
				}

				$cloudflare_update_result[] = [
					'result'  => 'success',
					// translators: %s is the caching level returned by the CloudFlare API.
					'message' => '<strong>' . __( 'WP Rocket: ', 'rocket' ) . '</strong>' . sprintf( __( 'Cloudflare cache level set to %s', 'rocket' ), $cf_cache_level_return ),
				];
			}

			// Active Minification for HTML, CSS & JS.
			$cf_minify        = isset( $cf_old_settings[1] ) && 0 === $value['cloudflare_auto_settings'] ? $cf_old_settings[1] : 'on';
			$cf_minify_return = $this->cloudflare->set_minify( $cf_minify );

			if ( is_wp_error( $cf_minify_return ) ) {
				$cloudflare_update_result[] = [
					'result'  => 'error',
					// translators: %s is the message returned by the CloudFlare API.
					'message' => '<strong>' . __( 'WP Rocket: ', 'rocket' ) . '</strong>' . sprintf( __( 'Cloudflare minification error: %s', 'rocket' ), $cf_minify_return->get_error_message() ),
				];
			} else {
				$cloudflare_update_result[] = [
					'result'  => 'success',
					// translators: %s is the message returned by the CloudFlare API.
					'message' => '<strong>' . __( 'WP Rocket: ', 'rocket' ) . '</strong>' . sprintf( __( 'Cloudflare minification %s', 'rocket' ), $cf_minify_return ),
				];
			}

			// Deactivate Rocket Loader to prevent conflicts.
			$cf_rocket_loader        = isset( $cf_old_settings[2] ) && 0 === $value['cloudflare_auto_settings'] ? $cf_old_settings[2] : 'off';
			$cf_rocket_loader_return = $this->cloudflare->set_rocket_loader( $cf_rocket_loader );

			if ( is_wp_error( $cf_rocket_loader_return ) ) {
				$cloudflare_update_result[] = [
					'result'  => 'error',
					// translators: %s is the message returned by the CloudFlare API.
					'message' => '<strong>' . __( 'WP Rocket: ', 'rocket' ) . '</strong>' . sprintf( __( 'Cloudflare rocket loader error: %s', 'rocket' ), $cf_rocket_loader_return->get_error_message() ),
				];
			} else {
				$cloudflare_update_result[] = [
					'result'  => 'success',
					// translators: %s is the message returned by the CloudFlare API.
					'message' => '<strong>' . __( 'WP Rocket: ', 'rocket' ) . '</strong>' . sprintf( __( 'Cloudflare rocket loader %s', 'rocket' ), $cf_rocket_loader_return ),
				];
			}

			// Set Browser cache to 1 year.
			$cf_browser_cache_ttl    = isset( $cf_old_settings[3] ) && 0 === $value['cloudflare_auto_settings'] ? $cf_old_settings[3] : '31536000';
			$cf_browser_cache_return = $this->cloudflare->set_browser_cache_ttl( $cf_browser_cache_ttl );

			if ( is_wp_error( $cf_browser_cache_return ) ) {
				$cloudflare_update_result[] = [
					'result'  => 'error',
					// translators: %s is the message returned by the CloudFlare API.
					'message' => '<strong>' . __( 'WP Rocket: ', 'rocket' ) . '</strong>' . sprintf( __( 'Cloudflare browser cache error: %s', 'rocket' ), $cf_browser_cache_return->get_error_message() ),
				];
			} else {
				$cloudflare_update_result[] = [
					'result'  => 'success',
					// translators: %s is the message returned by the CloudFlare API.
					'message' => '<strong>' . __( 'WP Rocket: ', 'rocket' ) . '</strong>' . sprintf( __( 'Cloudflare browser cache set to %s seconds', 'rocket' ), $cf_browser_cache_return ),
				];
			}
		}

		if ( [] !== $cloudflare_update_result ) {
			set_transient( get_current_user_id() . '_cloudflare_update_settings', $cloudflare_update_result );
		}
	}

	/**
	 * Save Cloudflare old settings when the auto settings option is enabled.
	 *
	 * @since 1.0
	 *
	 * @param array $value     An array of previous values for the settings.
	 * @param array $old_value An array of submitted values for the settings.
	 *
	 * @return array settings with old settings.
	 */
	public function save_cloudflare_old_settings( $value, $old_value ) {
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return $value;
		}

		// Save old CloudFlare settings.
		if (
			isset( $value['cloudflare_auto_settings'], $old_value ['cloudflare_auto_settings'] )
			&&
			$value['cloudflare_auto_settings'] !== $old_value ['cloudflare_auto_settings']
			&&
			1 === $value['cloudflare_auto_settings']
		) {
			$cf_settings                      = $this->cloudflare->get_settings();
			$value['cloudflare_old_settings'] = ! is_wp_error( $cf_settings )
				? implode( ',', array_filter( $cf_settings ) )
				: '';
		}

		return $value;
	}
}
