<?php
declare(strict_types=1);

namespace WP_Rocket\Addon\Cloudflare;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Admin\{Options, Options_Data};

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
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
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
			'rocket_buffer'                             => [ 'protocol_rewrite', PHP_INT_MAX ],
			'wp_calculate_image_srcset'                 => [ 'protocol_rewrite_srcset', PHP_INT_MAX ],
		];
	}

	/**
	 * Sets the Varnish IP to localhost if Cloudflare is active.
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
	 * @return bool
	 */
	private function should_filter_varnish(): bool {
		// This filter is documented in inc/Addon/Varnish.php.
		return apply_filters( 'do_rocket_varnish_http_purge', false ) // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
			||
			$this->options->get( 'varnish_auto_purge', 0 );
	}

	/**
	 * Automatically set Cloudflare development mode value to off after 3 hours to reflect Cloudflare behaviour.
	 */
	public function deactivate_devmode() {
		$this->options->set( 'cloudflare_devmode', 0 );
		$this->options_api->set( 'settings', $this->options->get_options() );
	}

	/**
	 * Purge Cloudflare cache automatically if Cache Everything is set as a Page Rule.
	 *
	 * @return void
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
	 */
	public function purge_cache_no_die() {
		if ( ! current_user_can( 'rocket_purge_cloudflare_cache' ) ) {
			return;
		}

		// Purge CloudFlare.
		$cf_purge        = $this->cloudflare->purge_cloudflare();
		$cf_purge_result = [
			'result'  => 'success',
			'message' => __( '<strong>WP Rocket:</strong> Cloudflare cache successfully purged.', 'rocket' ),
		];

		if ( is_wp_error( $cf_purge ) ) {
			$cf_purge_result = [
				'result'  => 'error',
				// translators: %s = CloudFare API return message.
				'message' => sprintf( __( '<strong>WP Rocket:</strong> %s', 'rocket' ), $cf_purge->get_error_message() ),
			];
		}

		set_transient( get_current_user_id() . '_cloudflare_purge_result', $cf_purge_result );
	}

	/**
	 * Purge CloudFlare cache.
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
	 */
	public function set_real_ip() {
		Cloudflare::set_ip_rewrite();
	}

	/**
	 * Save Cloudflare dev mode admin option.
	 *
	 * @param string $value New value for Cloudflare dev mode.
	 */
	private function save_cloudflare_devmode( $value ) {
		$result = $this->cloudflare->set_devmode( $value );

		if ( is_wp_error( $result ) ) {
			return [
				'result'  => 'error',
				// translators: %s is the message returned by the CloudFlare API.
				'message' => sprintf( __( 'Cloudflare development mode error: %s', 'rocket' ), $result->get_error_message() ),
			];
		}

		return [
			'result'  => 'success',
			// translators: %s is the message returned by the CloudFlare API.
			'message' => sprintf( __( 'Cloudflare development mode %s', 'rocket' ), $result ),
		];
	}

	/**
	 * Save Cloudflare cache_level admin option.
	 *
	 * @param string $value New value for Cloudflare cache_level.
	 */
	private function save_cache_level( $value ) {
		// Set Cache Level to Aggressive.
		$result = $this->cloudflare->set_cache_level( $value );

		if ( is_wp_error( $result ) ) {
			return [
				'result'  => 'error',
				// translators: %s is the message returned by the CloudFlare API.
				'message' => sprintf( __( 'Cloudflare cache level error: %s', 'rocket' ), $result->get_error_message() ),
			];
		}

		$level = $value;

		if ( 'aggressive' === $result ) {
			$level = _x( 'standard', 'Cloudflare caching level', 'rocket' );
		}

		return [
			'result'  => 'success',
			// translators: %s is the caching level returned by the CloudFlare API.
			'message' => sprintf( __( 'Cloudflare cache level set to %s', 'rocket' ), $level ),
		];
	}

	/**
	 * Save Cloudflare minify admin option.
	 *
	 * @param string $value New value for Cloudflare minify.
	 */
	private function save_minify( $value ) {
		$result = $this->cloudflare->set_minify( $value );

		if ( is_wp_error( $result ) ) {
			return [
				'result'  => 'error',
				// translators: %s is the message returned by the CloudFlare API.
				'message' => sprintf( __( 'Cloudflare minification error: %s', 'rocket' ), $result->get_error_message() ),
			];
		}

		return [
			'result'  => 'success',
			// translators: %s is the message returned by the CloudFlare API.
			'message' => sprintf( __( 'Cloudflare minification %s', 'rocket' ), $result ),
		];
	}

	/**
	 * Save Cloudflare rocket loader admin option.
	 *
	 * @param string $value New value for Cloudflare rocket loader.
	 */
	private function save_rocket_loader( $value ) {
		$result = $this->cloudflare->set_rocket_loader( $value );

		if ( is_wp_error( $result ) ) {
			return [
				'result'  => 'error',
				// translators: %s is the message returned by the CloudFlare API.
				'message' => sprintf( __( 'Cloudflare rocket loader error: %s', 'rocket' ), $result->get_error_message() ),
			];
		}

		return [
			'result'  => 'success',
			// translators: %s is the message returned by the CloudFlare API.
			'message' => sprintf( __( 'Cloudflare rocket loader %s', 'rocket' ), $result ),
		];
	}

	/**
	 * Save Cloudflare browser cache ttl admin option.
	 *
	 * @param int $value New value for Cloudflare browser cache ttl.
	 */
	private function save_browser_cache_ttl( $value ) {
		$result = $this->cloudflare->set_browser_cache_ttl( $value );

		if ( is_wp_error( $result ) ) {
			return [
				'result'  => 'error',
				// translators: %s is the message returned by the CloudFlare API.
				'message' => sprintf( __( 'Cloudflare browser cache error: %s', 'rocket' ), $result->get_error_message() ),
			];
		}

		return [
			'result'  => 'success',
			// translators: %s is the message returned by the CloudFlare API.
			'message' => sprintf( __( 'Cloudflare browser cache set to %s seconds', 'rocket' ), $result ),
		];
	}

	/**
	 * Save Cloudflare auto settings admin option.
	 *
	 * @param int    $auto_settings New value for Cloudflare auto_settings.
	 * @param string $old_settings Cloudflare cloudflare_old_settings.
	 */
	private function save_cloudflare_auto_settings( $auto_settings, $old_settings ) {
		$cf_old_settings = explode( ',', $old_settings );

		$result = [];

		// Set Cache Level to Aggressive.
		$cf_cache_level = isset( $cf_old_settings[0] ) && 0 === $auto_settings ? 'basic' : 'aggressive';
		$result[]       = $this->save_cache_level( $cf_cache_level );

		// Active Minification for CSS & JS.
		$cf_minify = isset( $cf_old_settings[1] ) && 0 === $auto_settings ? $cf_old_settings[1] : 'on';
		$result[]  = $this->save_minify( $cf_minify );

		// Deactivate Rocket Loader to prevent conflicts.
		$cf_rocket_loader = isset( $cf_old_settings[2] ) && 0 === $auto_settings ? $cf_old_settings[2] : 'off';
		$result[]         = $this->save_rocket_loader( $cf_rocket_loader );

		// Set Browser cache to 4 hours.
		$cf_browser_cache_ttl = isset( $cf_old_settings[3] ) && 0 === $auto_settings ? $cf_old_settings[3] : '14400';
		$result[]             = $this->save_browser_cache_ttl( $cf_browser_cache_ttl );

		return $result;
	}

	/**
	 * Save Cloudflare admin options.
	 *
	 * @param array $old_value An array of previous values for the settings.
	 * @param array $value     An array of submitted values for the settings.
	 *
	 * @return void
	 */
	public function save_cloudflare_options( $old_value, $value ) {
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		$valid_key      = get_transient( 'rocket_cloudflare_is_api_keys_valid' );
		$submit_cf_view = false;

		if (
			false === $valid_key
			||
			is_wp_error( $valid_key )
		) {
			$result = $this->cloudflare->is_auth_valid( $value['cloudflare_zone_id'] );
			set_transient( 'rocket_cloudflare_is_api_keys_valid', $result, 2 * WEEK_IN_SECONDS );
			$submit_cf_view = true;
			$valid_key      = $result;
		}

		if (
			(
				isset( $old_value['cloudflare_devmode'], $value['cloudflare_devmode'] )
				&&
				(int) $old_value['cloudflare_devmode'] !== (int) $value['cloudflare_devmode']
			)
			||
			(
				isset( $old_value['cloudflare_auto_settings'], $value['cloudflare_auto_settings'] )
				&&
				(int) $old_value['cloudflare_auto_settings'] !== (int) $value['cloudflare_auto_settings']
			)
		) {
			$submit_cf_view = true;
		}

		// If is submit CF view & CF Credentials are invalid, display error and bail out.
		if ( is_wp_error( $valid_key ) && $submit_cf_view ) {
			$error = $valid_key->get_error_message();

			add_settings_error( 'general', 'cloudflare_api_key_invalid', __( 'WP Rocket: ', 'rocket' ) . '</strong>' . $error . '<strong>', 'error' );

			set_transient( get_current_user_id() . '_cloudflare_update_settings', [] );

			return;
		}

		// Update CloudFlare Development Mode.
		$result = [];
		if (
			isset( $old_value['cloudflare_devmode'], $value['cloudflare_devmode'] )
			&&
			(int) $old_value['cloudflare_devmode'] !== (int) $value['cloudflare_devmode']
		) {
			$result[] = $this->save_cloudflare_devmode( $value['cloudflare_devmode'] );
		}

		// Update CloudFlare settings.
		if (
			isset( $old_value['cloudflare_auto_settings'], $value['cloudflare_auto_settings'] )
			&&
			(int) $old_value['cloudflare_auto_settings'] !== (int) $value['cloudflare_auto_settings']
		) {
			$result['pre'] = sprintf(
				// translators: %1$s = strong opening tag, %2$s = strong closing tag.
				__( '%1$sWP Rocket:%2$s Optimal settings activated for Cloudflare:', 'rocket' ),
				'<strong>',
				'</strong>'
			) . '<br>';

			if ( 0 === (int) $value['cloudflare_auto_settings'] ) {
				$result['pre'] = sprintf(
					// translators: %1$s = strong opening tag, %2$s = strong closing tag.
					__( '%1$sWP Rocket:%2$s Optimal settings deactivated for Cloudflare, reverted to previous settings:', 'rocket' ),
					'<strong>',
					'</strong>'
				) . '<br>';
			}

			$result = array_merge( $result, $this->save_cloudflare_auto_settings( $value['cloudflare_auto_settings'], $value['cloudflare_old_settings'] ) );
		}

		set_transient( get_current_user_id() . '_cloudflare_update_settings', $result );
	}

	/**
	 * Save Cloudflare old settings when the auto settings option is enabled.
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

	/**
	 * Remove HTTP protocol on script, link, img and form tags.
	 *
	 * @param string $buffer HTML content.
	 *
	 * @return string
	 */
	public function protocol_rewrite( $buffer ) {
		if ( ! $this->can_protocol_rewrite() ) {
			return $buffer;
		}

		$return = preg_replace( "/(<(script|link|img|form)(?!.*?[\"']\bcanonical\b[\"'])([^>]*)(href|src|action)=[\"'])https?:\\/\\//i", '$1//', $buffer );

		if ( $return ) {
			$buffer = $return;
		}

		return $buffer;
	}

	/**
	 * Remove HTTP protocol on srcset attribute generated by WordPress
	 *
	 * @param array $sources an Array of images sources for srcset.
	 *
	 * @return array
	 */
	public function protocol_rewrite_srcset( $sources ) {
		if ( ! $this->can_protocol_rewrite() ) {
			return $sources;
		}

		if ( empty( $sources ) ) {
			return $sources;
		}

		foreach ( $sources as $i => $source ) {
			$sources[ $i ]['url'] = str_replace( [ 'http:', 'https:' ], '', $source['url'] );
		}

		return $sources;
	}

	/**
	 * Can rewrite protocol
	 *
	 * @return bool
	 */
	private function can_protocol_rewrite(): bool {
		return $this->options->get( 'do_cloudflare', 0 )
		&&
		(
			$this->options->get( 'cloudflare_protocol_rewrite', 0 )
			||
			apply_filters( 'do_rocket_protocol_rewrite', false ) // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		);
	}
}
