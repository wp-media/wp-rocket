<?php
namespace WP_Rocket\Subscriber\Tools;

use WP_Rocket\Admin\Options_Data as Options;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Cloudflare Subscriber.
 *
 * @since  3.5
 * @author Soponar Cristina
 */
class Cloudflare_Subscriber implements Subscriber_Interface {

	/**
	 * Instance of the Option_Data class.
	 *
	 * @var    Options
	 * @since  3.5
	 * @access private
	 * @author Soponar Cristina
	 */
	private $options;

	/**
	 * Cloudflare User Agent
	 */
	const CF_USER_AGENT = 'wp-rocket/' . WP_ROCKET_VERSION;

	/**
	 * Constructor.
	 *
	 * @since  3.5
	 * @access public
	 * @author Soponar Cristina
	 *
	 * @param Options $options Instance of the Option_Data class.
	 */
	public function __construct( Options $options) {
		$this->options = $options;
	}

	/**
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		if ( ! get_rocket_option( 'do_cloudflare' ) ) {
			return;
		}

		return [
			'rocket_cron_deactivate_cloudflare_devmode' => 'deactivate_devmode',
			'after_rocket_clean_domain'                 => 'auto_purge',
			'after_rocket_clean_post'                   => [ 'auto_purge_by_url', 10, 3 ],
			'admin_post_rocket_purge_cloudflare'        => 'do_purge_cloudflare',
			'init'                                      => [ 'set_real_ip', 1 ],
			'admin_notices'                             => [
				[ 'maybe_print_notice' ],
				[ 'maybe_print_update_settings_notice' ],
			],
		];
	}

	/**
	 * Automatically set Cloudflare development mode value to off after 3 hours to reflect Cloudflare behaviour
	 *
	 * @since 2.9
	 * @author Remy Perona
	 */
	public function deactivate_devmode() {
		$options                       = $this->options->get( WP_ROCKET_SLUG );
		$options['cloudflare_devmode'] = 'off';
		update_option( WP_ROCKET_SLUG, $options );
	}

	/**
	 * Purge Cloudflare cache automatically if Cache Everything is set as a Page Rule
	 *
	 * @since 3.4.2
	 * @author Soponar Cristina
	 */
	public function auto_purge() {
		if ( ! get_rocket_option( 'do_cloudflare' ) || ! current_user_can( 'rocket_purge_cloudflare_cache' ) ) {
			return;
		}

		$cf_cache_everything = $this->has_page_rule( 'cache_everything' );

		if ( is_wp_error( $cf_cache_everything ) || ! $cf_cache_everything ) {
			return;
		}

		// Purge CloudFlare.
		$cf_purge = $this->purge_cloudflare();

		if ( is_wp_error( $cf_purge ) ) {
			$cf_purge_result = [
				'result'  => 'error',
				// translators: %s = CloudFare API return message.
				'message' => sprintf( __( 'Cloudflare cache purge error: %s', 'rocket' ), $cf_purge->get_error_message() ),
			];
		} else {
			$cf_purge_result = [
				'result'  => 'success',
				'message' => __( 'Cloudflare cache successfully purged', 'rocket' ),
			];
		}

		set_transient( get_current_user_id() . '_cloudflare_purge_result', $cf_purge_result );
	}

	/**
	 * Purge Cloudflare cache URLs automatically if Cache Everything is set as a Page Rule
	 *
	 * @since 3.4.2
	 * @author Soponar Cristina
	 *
	 * @param WP_Post $post       The post object.
	 * @param array   $purge_urls URLs cache files to remove.
	 * @param string  $lang       The post language.
	 */
	public function auto_purge_by_url( $post, $purge_urls, $lang ) {
		if ( ! get_rocket_option( 'do_cloudflare' ) || ! current_user_can( 'rocket_purge_cloudflare_cache' ) ) {
			return;
		}

		$cf_cache_everything = $this->has_page_rule( 'cache_everything' );

		if ( is_wp_error( $cf_cache_everything ) || ! $cf_cache_everything ) {
			return;
		}

		// Purge CloudFlare.
		$cf_purge = $this->purge_by_url( $post, $purge_urls, $lang );

		if ( is_wp_error( $cf_purge ) ) {
			$cf_purge_result = [
				'result'  => 'error',
				// translators: %s = CloudFare API return message.
				'message' => sprintf( __( 'Cloudflare cache purge error: %s', 'rocket' ), $cf_purge->get_error_message() ),
			];
		} else {
			$cf_purge_result = [
				'result'  => 'success',
				'message' => __( 'Cloudflare cache successfully purged', 'rocket' ),
			];
		}

		set_transient( get_current_user_id() . '_cloudflare_purge_result', $cf_purge_result );
	}

	/**
	 * Purge CloudFlare cache
	 *
	 * @since 2.5
	 */
	public function do_purge_cloudflare() {
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'rocket_purge_cloudflare' ) ) {
			wp_nonce_ays( '' );
		}

		if ( ! current_user_can( 'rocket_purge_cloudflare_cache' ) ) {
			return;
		}

		// Purge CloudFlare.
		$cf_purge = $this->purge_cloudflare();

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

		wp_safe_redirect( esc_url_raw( wp_get_referer() ) );
		die();
	}

	/**
	 * Set Real IP from CloudFlare
	 *
	 * @since 2.8.16 Uses CloudFlare API v4 to get CloudFlare IPs
	 * @since 2.5.4
	 * @source cloudflare.php - https://wordpress.org/plugins/cloudflare/
	 */
	public function set_real_ip() {
		$is_cf = ( isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) ? true : false;

		if ( ! $is_cf ) {
			return;
		}
		// only run this logic if the REMOTE_ADDR is populated, to avoid causing notices in CLI mode.
		if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$cf_ips_values = $this->get_cloudflare_ips();

			if ( strpos( $_SERVER['REMOTE_ADDR'], ':' ) === false ) {
				$cf_ip_ranges = $cf_ips_values->result->ipv4_cidrs;

				// IPV4: Update the REMOTE_ADDR value if the current REMOTE_ADDR value is in the specified range.
				foreach ( $cf_ip_ranges as $range ) {
					if ( rocket_ipv4_in_range( $_SERVER['REMOTE_ADDR'], $range ) ) {
						if ( $_SERVER['HTTP_CF_CONNECTING_IP'] ) {
							$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
						}
						break;
					}
				}
			} else {
				$cf_ip_ranges = $cf_ips_values->result->ipv6_cidrs;

				$ipv6 = get_rocket_ipv6_full( $_SERVER['REMOTE_ADDR'] );
				foreach ( $cf_ip_ranges as $range ) {
					if ( rocket_ipv6_in_range( $ipv6, $range ) ) {
						if ( $_SERVER['HTTP_CF_CONNECTING_IP'] ) {
							$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
						}
						break;
					}
				}
			}
		}

		// Let people know that the CF WP plugin is turned on.
		if ( ! headers_sent() ) {
			header( 'X-CF-Powered-By: WP Rocket ' . WP_ROCKET_VERSION );
		}
	}

	/**
	 * Validate Cloudflare input data
	 *
	 * @since 3.5
	 * @author Soponar Cristina
	 *
	 * @param string $cf_email           - Cloudflare email.
	 * @param string $cf_api_key         - Cloudflare API key.
	 * @param string $cf_zone_id         - Cloudflare zone ID.
	 * @return Object                    - true if credentials are ok, WP_Error otherwise.
	 */
	public static function is_api_keys_valid( $cf_email, $cf_api_key, $cf_zone_id ) {
		if ( ! function_exists( 'curl_init' ) || ! function_exists( 'curl_exec' ) ) {
			return new \WP_Error( 'curl_disabled', __( 'Curl is disabled on your server. Please ask your host to enable it. This is required for the Cloudflare Add-on to work correctly.', 'rocket' ) );
		}

		if ( ! isset( $cf_email, $cf_api_key ) || empty( $cf_email ) || empty( $cf_api_key ) ) {
			return new \WP_Error(
				'cloudflare_credentials_empty',
				sprintf(
					/* translators: %1$s = opening link; %2$s = closing link */
					__( 'Cloudflare email, API key and Zone ID are not set. Read the %1$sdocumentation%2$s for further guidance.', 'rocket' ),
					// translators: Documentation exists in EN, FR; use localized URL if applicable.
					'<a href="' . esc_url( __( 'https://docs.wp-rocket.me/article/18-using-wp-rocket-with-cloudflare/?utm_source=wp_plugin&utm_medium=wp_rocket#add-on', 'rocket' ) ) . '" rel="noopener noreferrer" target="_blank">',
					'</a>'
				)
			);
		}

		if ( ! isset( $cf_zone_id ) || empty( $cf_zone_id ) ) {
			$msg = __( 'Missing Cloudflare Zone ID.', 'rocket' );

			$msg .= ' ' . sprintf(
				/* translators: %1$s = opening link; %2$s = closing link */
				__( 'Read the %1$sdocumentation%2$s for further guidance.', 'rocket' ),
				// translators: Documentation exists in EN, FR; use localized URL if applicable.
				'<a href="' . esc_url( __( 'https://docs.wp-rocket.me/article/18-using-wp-rocket-with-cloudflare/?utm_source=wp_plugin&utm_medium=wp_rocket#add-on', 'rocket' ) ) . '" rel="noopener noreferrer" target="_blank">',
				'</a>'
			);

			return new \WP_Error( 'cloudflare_no_zone_id', $msg );
		}

		try {
			$cf_api_instance = new \Cloudflare\Api( $cf_email, $cf_api_key );
			$cf_api_instance->setCurlOption( CURLOPT_USERAGENT, self::CF_USER_AGENT );

			$cf_zone = $cf_api_instance->get( 'zones/' . $cf_zone_id );

			if ( ! isset( $cf_zone->success ) || empty( $cf_zone->success ) ) {
				foreach ( $cf_zone->errors as $error ) {
					if ( $error->code === 6003 ) {
						$msg = __( 'Incorrect Cloudflare email address or API key.', 'rocket' );

						$msg .= ' ' . sprintf(
							/* translators: %1$s = opening link; %2$s = closing link */
							__( 'Read the %1$sdocumentation%2$s for further guidance.', 'rocket' ),
							// translators: Documentation exists in EN, FR; use localized URL if applicable.
							'<a href="' . esc_url( __( 'https://docs.wp-rocket.me/article/18-using-wp-rocket-with-cloudflare/?utm_source=wp_plugin&utm_medium=wp_rocket#add-on', 'rocket' ) ) . '" rel="noopener noreferrer" target="_blank">',
							'</a>'
						);

						return new \WP_Error( 'cloudflare_invalid_auth', $msg );
					}
				}
				$msg = __( 'Incorrect Cloudflare Zone ID.', 'rocket' );

				$msg .= ' ' . sprintf(
					/* translators: %1$s = opening link; %2$s = closing link */
					__( 'Read the %1$sdocumentation%2$s for further guidance.', 'rocket' ),
					// translators: Documentation exists in EN, FR; use localized URL if applicable.
					'<a href="' . esc_url( __( 'https://docs.wp-rocket.me/article/18-using-wp-rocket-with-cloudflare/?utm_source=wp_plugin&utm_medium=wp_rocket#add-on', 'rocket' ) ) . '" rel="noopener noreferrer" target="_blank">',
					'</a>'
				);
				return new \WP_Error( 'cloudflare_invalid_auth', $msg );
			}

			if ( true === $cf_zone->success ) {
				$zone_found = false;
				$site_url   = get_site_url();

				if ( function_exists( 'domain_mapping_siteurl' ) ) {
					$site_url = domain_mapping_siteurl( $site_url );
				}

				if ( ! empty( $cf_zone->result ) ) {
					$parsed_url = wp_parse_url( $site_url );
					if ( false !== strpos( strtolower( $parsed_url['host'] ), $cf_zone->result->name ) ) {
						$zone_found = true;
					}
				}

				if ( ! $zone_found ) {
					$msg = __( 'It looks like your domain is not set up on Cloudflare.', 'rocket' );

					$msg .= ' ' . sprintf(
						/* translators: %1$s = opening link; %2$s = closing link */
						__( 'Read the %1$sdocumentation%2$s for further guidance.', 'rocket' ),
						// translators: Documentation exists in EN, FR; use localized URL if applicable.
						'<a href="' . esc_url( __( 'https://docs.wp-rocket.me/article/18-using-wp-rocket-with-cloudflare/?utm_source=wp_plugin&utm_medium=wp_rocket#add-on', 'rocket' ) ) . '" rel="noopener noreferrer" target="_blank">',
						'</a>'
					);
					return new \WP_Error( 'cloudflare_wrong_zone_id', $msg );
				}

				return true;
			}
		} catch ( \Exception $e ) {
			$msg  = __( 'Incorrect Cloudflare email address or API key.', 'rocket' );
			$msg .= ' ' . sprintf(
				/* translators: %1$s = opening link; %2$s = closing link */
				__( 'Read the %1$sdocumentation%2$s for further guidance.', 'rocket' ),
				// translators: Documentation exists in EN, FR; use localized URL if applicable.
				'<a href="' . esc_url( __( 'https://docs.wp-rocket.me/article/18-using-wp-rocket-with-cloudflare/?utm_source=wp_plugin&utm_medium=wp_rocket#add-on', 'rocket' ) ) . '" rel="noopener noreferrer" target="_blank">',
				'</a>'
			);
			return new \WP_Error( 'cloudflare_invalid_auth', $msg );
		}
	}

	/**
	 * Get a Cloudflare\Api instance & the zone_id corresponding to the domain
	 *
	 * @since 2.8.21 Get the zone ID from the options
	 * @since 2.8.18 Add try/catch to prevent fatal error Uncaugh Exception
	 * @since 2.8.16 Update to Cloudflare API v4
	 * @since 2.5
	 *
	 * @return Object Cloudflare instance & zone_id if credentials are correct, WP_Error otherwise
	 */
	public static function get_instance() {
		$cf_email             = get_rocket_option( 'cloudflare_email', null );
		$cf_api_key           = ( defined( 'WP_ROCKET_CF_API_KEY' ) ) ? WP_ROCKET_CF_API_KEY : get_rocket_option( 'cloudflare_api_key', null );
		$cf_zone_id           = get_rocket_option( 'cloudflare_zone_id', null );
		$is_api_keys_valid_cf = get_transient( 'rocket_cloudflare_is_api_keys_valid' );

		if ( false === $is_api_keys_valid_cf ) {
			$is_api_keys_valid_cf = self::is_api_keys_valid( $cf_email, $cf_api_key, $cf_zone_id );
			set_transient( 'rocket_cloudflare_is_api_keys_valid', $is_api_keys_valid_cf, 2 * WEEK_IN_SECONDS );
		}

		if ( is_wp_error( $is_api_keys_valid_cf ) ) {
			return $is_api_keys_valid_cf;
		}

		$cf_api_instance = new \Cloudflare\Api( $cf_email, $cf_api_key );
		$cf_api_instance->setCurlOption( CURLOPT_USERAGENT, self::CF_USER_AGENT );

		$cf_instance = (object) [
			'auth'    => $cf_api_instance,
			'zone_id' => $cf_zone_id,
		];

		return $cf_instance;
	}

	/**
	 * Get all the current Cloudflare settings for a given domain.
	 *
	 * @since 2.8.16 Update to Cloudflare API v4
	 * @since 2.5
	 *
	 * @return mixed bool|Array Array of Cloudflare settings, false if any error connection to Cloudflare
	 */
	public static function get_settings() {
		$cf_instance = self::get_instance();
		if ( is_wp_error( $cf_instance ) ) {
			return $cf_instance;
		}

		try {
			$cf_settings_instance = new \Cloudflare\Zone\Settings( $cf_instance->auth );
			$cf_settings          = $cf_settings_instance->settings( $cf_instance->zone_id );
			$cf_minify            = $cf_settings->result[16]->value;
			$cf_minify_value      = 'on';

			if ( 'off' === $cf_minify->js || 'off' === $cf_minify->css || 'off' === $cf_minify->html ) {
				$cf_minify_value = 'off';
			}

			$cf_settings_array = array(
				'cache_level'       => $cf_settings->result[5]->value,
				'minify'            => $cf_minify_value,
				'rocket_loader'     => $cf_settings->result[25]->value,
				'browser_cache_ttl' => $cf_settings->result[3]->value,
			);

			return $cf_settings_array;
		} catch ( \Exception $e ) {
			return new \WP_Error( 'cloudflare_current_settings', $e->getMessage() );
		}
	}

	/**
	 * Set the Cloudflare Development mode.
	 *
	 * @since 2.9 Now returns a value
	 * @since 2.8.16 Update to Cloudflare API v4
	 * @since 2.5
	 *
	 * @param string $mode Value for Cloudflare development mode.
	 * @return mixed Object|String Mode value if the update is successful, WP_Error otherwise
	 */
	public static function set_devmode( $mode ) {
		$cf_instance = self::get_instance();

		if ( is_wp_error( $cf_instance ) ) {
			return $cf_instance;
		}

		if ( (int) 0 === $mode ) {
			$value = 'off';
		} elseif ( (int) 1 === $mode ) {
			$value = 'on';
		}

		try {
			$cf_settings = new \Cloudflare\Zone\Settings( $cf_instance->auth );
			$cf_return   = $cf_settings->change_development_mode( $cf_instance->zone_id, $value );

			if ( ! isset( $cf_return->success ) || empty( $cf_return->success ) ) {
				foreach ( $cf_return->errors as $error ) {
					$errors[] = $error->message;
				}

				$errors = implode( ', ', $errors );
				return new \WP_Error( 'cloudflare_dev_mode', $errors );
			}

			if ( 'on' === $value ) {
				wp_schedule_single_event( time() + 3 * HOUR_IN_SECONDS, 'rocket_cron_deactivate_cloudflare_devmode' );
			}

			return $value;
		} catch ( \Exception $e ) {
			return new \WP_Error( 'cloudflare_dev_mode', $e->getMessage() );
		}
	}

	/**
	 * Set the Cloudflare Caching level.
	 *
	 * @since 2.9 Now returns a value
	 * @since 2.8.16 Update to Cloudflare API v4
	 * @since 2.5
	 *
	 * @param string $mode Value for Cloudflare caching level.
	 * @return mixed Object|String Mode value if the update is successful, WP_Error otherwise
	 */
	public static function set_cache_level( $mode ) {
		$cf_instance = self::get_instance();
		if ( is_wp_error( $cf_instance ) ) {
			return $cf_instance;
		}

		try {
			$cf_settings = new \Cloudflare\Zone\Settings( $cf_instance->auth );
			$cf_return   = $cf_settings->change_cache_level( $cf_instance->zone_id, $mode );

			if ( ! isset( $cf_return->success ) || empty( $cf_return->success ) ) {
				foreach ( $cf_return->errors as $error ) {
					$errors[] = $error->message;
				}

				$errors = implode( ', ', $errors );
				return new \WP_Error( 'cloudflare_cache_level', $errors );
			}

			return $mode;
		} catch ( \Exception $e ) {
			return new \WP_Error( 'cloudflare_cache_level', $e->getMessage() );
		}
	}

	/**
	 * Set the Cloudflare Minification.
	 *
	 * @since 2.9 Now returns a value
	 * @since 2.8.16 Update to Cloudflare API v4
	 * @since 2.5
	 *
	 * @param string $mode Value for Cloudflare minification.
	 * @return mixed Object|String Mode value if the update is successful, WP_Error otherwise
	 */
	public static function set_minify( $mode ) {
		$cf_instance = self::get_instance();
		if ( is_wp_error( $cf_instance ) ) {
			return $cf_instance;
		}

		$cf_minify_settings = array(
			'css'  => $mode,
			'html' => $mode,
			'js'   => $mode,
		);

		try {
			$cf_settings = new \Cloudflare\Zone\Settings( $cf_instance->auth );
			$cf_return   = $cf_settings->change_minify( $cf_instance->zone_id, $cf_minify_settings );

			if ( ! isset( $cf_return->success ) || empty( $cf_return->success ) ) {
				foreach ( $cf_return->errors as $error ) {
					$errors[] = $error->message;
				}

				$errors = implode( ', ', $errors );
				return new \WP_Error( 'cloudflare_minification', $errors );
			}

			return $mode;
		} catch ( \Exception $e ) {
			return new \WP_Error( 'cloudflare_minification', $e->getMessage() );
		}
	}

	/**
	 * Set the Cloudflare Rocket Loader.
	 *
	 * @since 2.9 Now returns value
	 * @since 2.8.16 Update to Cloudflare API v4
	 * @since 2.5
	 *
	 * @param string $mode Value for Cloudflare Rocket Loader.
	 * @return mixed Object|String Mode value if the update is successful, WP_Error otherwise
	 */
	public static function set_rocket_loader( $mode ) {
		$cf_instance = self::get_instance();
		if ( is_wp_error( $cf_instance ) ) {
			return $cf_instance;
		}

		try {
			$cf_settings = new \Cloudflare\Zone\Settings( $cf_instance->auth );
			$cf_return   = $cf_settings->change_rocket_loader( $cf_instance->zone_id, $mode );

			if ( ! isset( $cf_return->success ) || empty( $cf_return->success ) ) {
				foreach ( $cf_return->errors as $error ) {
					$errors[] = $error->message;
				}

				$errors = implode( ', ', $errors );
				return new \WP_Error( 'cloudflare_rocket_loader', $errors );
			}

			return $mode;
		} catch ( \Exception $e ) {
			return new \WP_Error( 'cloudflare_rocket_loader', $e->getMessage() );
		}
	}

	/**
	 * Set the Browser Cache TTL in Cloudflare.
	 *
	 * @since 2.9 Now returns value
	 * @since 2.8.16
	 *
	 * @param string $mode Value for Cloudflare browser cache TTL.
	 * @return mixed Object|String Mode value if the update is successful, WP_Error otherwise
	 */
	public static function set_browser_cache_ttl( $mode ) {
		$cf_instance = self::get_instance();
		if ( is_wp_error( $cf_instance ) ) {
			return $cf_instance;
		}

		try {
			$cf_settings = new \Cloudflare\Zone\Settings( $cf_instance->auth );
			$cf_return   = $cf_settings->change_browser_cache_ttl( $cf_instance->zone_id, (int) $mode );

			if ( ! isset( $cf_return->success ) || empty( $cf_return->success ) ) {
				foreach ( $cf_return->errors as $error ) {
					$errors[] = $error->message;
				}

				$errors = implode( ', ', $errors );
				return new \WP_Error( 'cloudflare_browser_cache', $errors );
			}

			return $mode;
		} catch ( \Exception $e ) {
			return new \WP_Error( 'cloudflare_browser_cache', $e->getMessage() );
		}
	}

	/**
	 * Purge Cloudflare cache.
	 *
	 * @since 2.9 Now returns value
	 * @since 2.8.16 Update to Cloudflare API v4
	 * @since 2.5
	 *
	 * @return mixed Object|bool true if the purge is successful, WP_Error otherwise
	 */
	public static function purge_cloudflare() {
		$cf_instance = self::get_instance();
		if ( is_wp_error( $cf_instance ) ) {
			return $cf_instance;
		}

		try {
			$cf_cache = new \Cloudflare\Zone\Cache( $cf_instance->auth );
			$cf_purge = $cf_cache->purge( $cf_instance->zone_id, true );

			if ( ! isset( $cf_purge->success ) || empty( $cf_purge->success ) ) {
				foreach ( $cf_purge->errors as $error ) {
					$errors[] = $error->message;
				}

				$errors = implode( ', ', $errors );
				return new \WP_Error( 'cloudflare_purge_failed', $errors );
			}

			return true;
		} catch ( \Exception $e ) {
			return new \WP_Error( 'cloudflare_purge_failed', $e->getMessage() );
		}
	}


	/**
	 * Get Cloudflare IPs. No API validation needed, all exceptions returns the default CF IPs array.
	 *
	 * @since 2.8.21 Save IPs in a transient to prevent calling the API everytime
	 * @since 2.8.16
	 *
	 * @author Remy Perona
	 *
	 * @return Object Result of API request if successful, default CF IPs otherwise
	 */
	public function get_cloudflare_ips() {
		$cf_ips = get_transient( 'rocket_cloudflare_ips' );
		if ( false === $cf_ips ) {
			$cf_email        = get_rocket_option( 'cloudflare_email', null );
			$cf_api_key      = ( defined( 'WP_ROCKET_CF_API_KEY' ) ) ? WP_ROCKET_CF_API_KEY : get_rocket_option( 'cloudflare_api_key', null );
			$cf_api_instance = new \Cloudflare\Api( $cf_email, $cf_api_key );
			$cf_api_instance->setCurlOption( CURLOPT_USERAGENT, self::CF_USER_AGENT );

			try {
				$cf_ips_instance = new \Cloudflare\IPs( $cf_api_instance );
				$cf_ips          = $cf_ips_instance->ips();

				if ( ! isset( $cf_ips->success ) || ! $cf_ips->success ) {
					// Set default IPs from Cloudflare if call to Cloudflare /ips API does not contain a success.
					// Prevents from making API calls on each page load.
					$cf_ips = self::get_default_ips();
				}

				set_transient( 'rocket_cloudflare_ips', $cf_ips, 2 * WEEK_IN_SECONDS );
			} catch ( \Exception $e ) {
				// Set default IPs from Cloudflare if call to Cloudflare /ips API fails.
				// Prevents from making API calls on each page load.
				$cf_ips = self::get_default_ips();
				set_transient( 'rocket_cloudflare_ips', $cf_ips, 2 * WEEK_IN_SECONDS );
				return $cf_ips;
			}
		}

		return $cf_ips;
	}

	/**
	 * Purge Cloudflare Cache by URL
	 *
	 * @since 3.4.2
	 * @author Soponar Cristina
	 *
	 * @param WP_Post $post       The post object.
	 * @param array   $purge_urls URLs cache files to remove.
	 * @param string  $lang       The post language.
	 *
	 * @return mixed Object|bool  true if the purge is successful, WP_Error otherwise
	 */
	public function purge_by_url( $post, $purge_urls, $lang ) {
		$cf_instance = self::get_instance();
		if ( is_wp_error( $cf_instance ) ) {
			return $cf_instance;
		}

		try {
			$cf_cache = new \Cloudflare\Zone\Cache( $cf_instance->auth );
			$cf_purge = $cf_cache->purge_files( $cf_instance->zone_id, $purge_urls );

			if ( empty( $cf_purge->success ) ) {
				foreach ( $cf_purge->errors as $error ) {
					$errors[] = $error->message;
				}

				$errors = implode( ', ', $errors );
				return new \WP_Error( 'cloudflare_purge_failed', $errors );
			}

			return true;

		} catch ( \Exception $e ) {
			return new \WP_Error( 'cloudflare_purge_failed', $e->getMessage() );
		}
	}


	/**
	 * Checks if CF has the $action_value set as a Page Rule.
	 *
	 * @since  3.4.2
	 * @author Soponar Cristina
	 *
	 * @param  String $action_value - cache_everything.
	 * @return mixed  Object|bool   - true / false if $action_value was found or not, WP_Error otherwise
	 */
	private function has_page_rule( $action_value ) {
		$cf_instance = self::get_instance();
		if ( is_wp_error( $cf_instance ) ) {
			return $cf_instance;
		}

		try {
			$cf_page_rules = new \Cloudflare\Zone\Pagerules( $cf_instance->auth );
			$cf_page_rule  = $cf_page_rules->list_pagerules( $cf_instance->zone_id, 'active' );

			if ( empty( $cf_page_rule->success ) ) {
				foreach ( $cf_page_rule->errors as $error ) {
					$errors[] = $error->message;
				}

				$errors = implode( ', ', $errors );
				return new \WP_Error( 'cloudflare_page_rule_failed', $errors );
			}

			$cf_page_rule_arr = (array) $cf_page_rule;
			return in_array( $action_value, $cf_page_rule_arr, true );
		} catch ( \Exception $e ) {
			return new \WP_Error( 'cloudflare_page_rule_failed', $e->getMessage() );
		}
	}

	/**
	 * This notice is displayed after purging the CloudFlare cache
	 *
	 * @since 2.9
	 * @author Remy Perona
	 */
	public function maybe_print_notice() {
		if ( ! current_user_can( 'rocket_purge_cloudflare_cache' ) || ! is_admin() ) {
			return;
		}

		$user_id = get_current_user_id();
		$notice  = get_transient( $user_id . '_cloudflare_purge_result' );
		if ( ! $notice ) {
			return;
		}

		delete_transient( $user_id . '_cloudflare_purge_result' );

		\rocket_notice_html(
			[
				'status'  => $notice['result'],
				'message' => $notice['message'],
			]
		);
	}

	/**
	 * This notice is displayed after modifying the CloudFlare settings
	 *
	 * @since 2.9
	 * @author Remy Perona
	 */
	public function maybe_print_update_settings_notice() {
		$screen = get_current_screen();

		if ( ! current_user_can( 'rocket_manage_options' ) || 'settings_page_wprocket' !== $screen->id ) {
			return;
		}

		$user_id = get_current_user_id();
		$notices = get_transient( $user_id . '_cloudflare_update_settings' );
		if ( $notices ) {
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
				\rocket_notice_html(
					[
						'message' => $success,
					]
				);
			}

			if ( ! empty( $errors ) ) {
				\rocket_notice_html(
					[
						'status'  => 'error',
						'message' => $errors,
					]
				);
			}
		}
	}

	/**
	 * Get default Cloudflare IPs.
	 *
	 * @since  3.5
	 * @author Soponar Cristina
	 *
	 * @return Object Default Cloudflare connecting IPs.
	 */
	public static function get_default_ips() {
		$cf_ips = (object) [
			'success' => true,
			'result'  => (object) [],
		];

		$cf_ips->result->ipv4_cidrs = [
			'103.21.244.0/22',
			'103.22.200.0/22',
			'103.31.4.0/22',
			'104.16.0.0/12',
			'108.162.192.0/18',
			'131.0.72.0/22',
			'141.101.64.0/18',
			'162.158.0.0/15',
			'172.64.0.0/13',
			'173.245.48.0/20',
			'188.114.96.0/20',
			'190.93.240.0/20',
			'197.234.240.0/22',
			'198.41.128.0/17',
		];

		$cf_ips->result->ipv6_cidrs = [
			'2400:cb00::/32',
			'2405:8100::/32',
			'2405:b500::/32',
			'2606:4700::/32',
			'2803:f800::/32',
			'2c0f:f248::/32',
			'2a06:98c0::/29',
		];

		return $cf_ips;
	}
}
