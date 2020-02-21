<?php

namespace WPMedia\Cloudflare;

use Exception;
use stdClass;
use WP_Error;
use WP_Rocket\Admin\Options_Data;

/**
 * Cloudflare
 *
 * @since  1.0
 */
class Cloudflare {

	/**
	 * Options Data instance.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Cloudflare API instance.
	 *
	 * @var APIClient
	 */
	private $api;

	/**
	 * WP_Error if Cloudflare Credentials are not valid.
	 *
	 * @var WP_Error
	 */
	private $cloudflare_api_error;

	/**
	 * Creates an instance of Cloudflare Addon.
	 *
	 * @param Options_Data $options WP Rocket options instance.
	 * @param APIClient    $api     Cloudflare API instance.
	 */
	public function __construct( Options_Data $options, APIClient $api ) {
		$this->options              = $options;
		$this->cloudflare_api_error = null;
		$this->api                  = $api;
		// Update api_error with WP_Error if credentials are not valid.
		// Update API with Cloudflare instance with correct auth data.
		$this->get_cloudflare_instance();
	}

	/**
	 * Get a Cloudflare\Api instance & the zone_id corresponding to the domain.
	 *
	 * @since 1.0
	 *
	 * @return Object Cloudflare instance & zone_id if credentials are correct, WP_Error otherwise.
	 */
	public function get_cloudflare_instance() {
		$cf_email             = $this->options->get( 'cloudflare_email', null );
		$cf_api_key           = defined( 'WP_ROCKET_CF_API_KEY' ) ? WP_ROCKET_CF_API_KEY : $this->options->get( 'cloudflare_api_key', null );
		$cf_zone_id           = $this->options->get( 'cloudflare_zone_id', null );
		$is_api_keys_valid_cf = get_transient( 'rocket_cloudflare_is_api_keys_valid' );

		if ( false === $is_api_keys_valid_cf ) {
			$is_api_keys_valid_cf = $this->is_api_keys_valid( $cf_email, $cf_api_key, $cf_zone_id );
			set_transient( 'rocket_cloudflare_is_api_keys_valid', $is_api_keys_valid_cf, 2 * WEEK_IN_SECONDS );
		}

		if ( is_wp_error( $is_api_keys_valid_cf ) ) {
			// Sets Cloudflare API as WP_Error if credentials are not valid.
			$this->cloudflare_api_error = $is_api_keys_valid_cf;

			return;
		}

		// Sets Cloudflare Valid Credentials and User Agent.
		$this->api->set_api_credentials( $cf_email, $cf_api_key, $cf_zone_id );
	}

	/**
	 * Validate Cloudflare input data.
	 *
	 * @since 1.0
	 *
	 * @param string $cf_email   Cloudflare email.
	 * @param string $cf_api_key Cloudflare API key.
	 * @param string $cf_zone_id Cloudflare zone ID.
	 *
	 * @return stdClass true if credentials are ok, WP_Error otherwise.
	 */
	public function is_api_keys_valid( $cf_email, $cf_api_key, $cf_zone_id ) {
		if ( ! function_exists( 'curl_init' ) || ! function_exists( 'curl_exec' ) ) {
			return new WP_Error( 'curl_disabled', __( 'Curl is disabled on your server. Please ask your host to enable it. This is required for the Cloudflare Add-on to work correctly.', 'rocket' ) );
		}

		if ( empty( $cf_email ) || empty( $cf_api_key ) ) {
			return new WP_Error(
				'cloudflare_credentials_empty',
				sprintf(
					/* translators: %1$s = opening link; %2$s = closing link */
					__( 'Cloudflare email and/or API key are not set. Read the %1$sdocumentation%2$s for further guidance.', 'rocket' ),
					// translators: Documentation exists in EN, FR; use localized URL if applicable.
					'<a href="' . esc_url( __( 'https://docs.wp-rocket.me/article/18-using-wp-rocket-with-cloudflare/?utm_source=wp_plugin&utm_medium=wp_rocket#add-on', 'rocket' ) ) . '" rel="noopener noreferrer" target="_blank">',
					'</a>'
				)
			);
		}

		if ( empty( $cf_zone_id ) ) {
			$msg = __( 'Missing Cloudflare Zone ID.', 'rocket' );

			$msg .= ' ' . sprintf(
				/* translators: %1$s = opening link; %2$s = closing link */
				__( 'Read the %1$sdocumentation%2$s for further guidance.', 'rocket' ),
				// translators: Documentation exists in EN, FR; use localized URL if applicable.
				'<a href="' . esc_url( __( 'https://docs.wp-rocket.me/article/18-using-wp-rocket-with-cloudflare/?utm_source=wp_plugin&utm_medium=wp_rocket#add-on', 'rocket' ) ) . '" rel="noopener noreferrer" target="_blank">',
				'</a>'
			);

			return new WP_Error( 'cloudflare_no_zone_id', $msg );
		}

		try {
			$this->api->set_api_credentials( $cf_email, $cf_api_key, $cf_zone_id );

			$cf_zone = $this->api->get_zones();

			if ( empty( $cf_zone->success ) ) {
				foreach ( $cf_zone->errors as $error ) {
					if ( 6003 === $error->code ) {
						$msg = __( 'Incorrect Cloudflare email address or API key.', 'rocket' );

						$msg .= ' ' . sprintf(
							/* translators: %1$s = opening link; %2$s = closing link */
							__( 'Read the %1$sdocumentation%2$s for further guidance.', 'rocket' ),
							// translators: Documentation exists in EN, FR; use localized URL if applicable.
							'<a href="' . esc_url( __( 'https://docs.wp-rocket.me/article/18-using-wp-rocket-with-cloudflare/?utm_source=wp_plugin&utm_medium=wp_rocket#add-on', 'rocket' ) ) . '" rel="noopener noreferrer" target="_blank">',
							'</a>'
						);

						return new WP_Error( 'cloudflare_invalid_auth', $msg );
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

				return new WP_Error( 'cloudflare_invalid_auth', $msg );
			}

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

				return new WP_Error( 'cloudflare_wrong_zone_id', $msg );
			}

			return true;
		} catch ( Exception $e ) {
			$msg  = __( 'Incorrect Cloudflare email address or API key.', 'rocket' );
			$msg .= ' ' . sprintf(
				/* translators: %1$s = opening link; %2$s = closing link */
				__( 'Read the %1$sdocumentation%2$s for further guidance.', 'rocket' ),
				// translators: Documentation exists in EN, FR; use localized URL if applicable.
				'<a href="' . esc_url( __( 'https://docs.wp-rocket.me/article/18-using-wp-rocket-with-cloudflare/?utm_source=wp_plugin&utm_medium=wp_rocket#add-on', 'rocket' ) ) . '" rel="noopener noreferrer" target="_blank">',
				'</a>'
			);

			return new WP_Error( 'cloudflare_invalid_auth', $msg );
		}
	}

	/**
	 * Checks if CF has the $action_value set as a Page Rule.
	 *
	 * @since 1.0
	 *
	 * @param string $action_value Cache_everything.
	 *
	 * @return mixed  Object|bool true / false if $action_value was found or not, WP_Error otherwise.
	 */
	public function has_page_rule( $action_value ) {
		if ( is_wp_error( $this->cloudflare_api_error ) ) {
			return $this->cloudflare_api_error;
		}

		try {
			$cf_page_rule = $this->api->list_pagerules();

			if ( empty( $cf_page_rule->success ) ) {
				foreach ( $cf_page_rule->errors as $error ) {
					$errors[] = $error->message;
				}

				$errors = wp_sprintf_l( '%l ', $errors );

				return new WP_Error( 'cloudflare_page_rule_failed', $errors );
			}

			$cf_page_rule_arr = wp_json_encode( $cf_page_rule );

			return preg_match( '/' . $action_value . '/', $cf_page_rule_arr );
		} catch ( Exception $e ) {
			return new WP_Error( 'cloudflare_page_rule_failed', $e->getMessage() );
		}
	}

	/**
	 * Purge Cloudflare cache.
	 *
	 * @since 1.0
	 *
	 * @return mixed Object|bool true if the purge is successful, WP_Error otherwise.
	 */
	public function purge_cloudflare() {
		if ( is_wp_error( $this->cloudflare_api_error ) ) {
			return $this->cloudflare_api_error;
		}

		try {
			$cf_purge = $this->api->purge();

			if ( empty( $cf_purge->success ) ) {
				foreach ( $cf_purge->errors as $error ) {
					$errors[] = $error->message;
				}

				$errors = wp_sprintf_l( '%l ', $errors );

				return new WP_Error( 'cloudflare_purge_failed', $errors );
			}

			return true;
		} catch ( Exception $e ) {
			return new WP_Error( 'cloudflare_purge_failed', $e->getMessage() );
		}
	}

	/**
	 * Purge Cloudflare Cache by URL
	 *
	 * @since 1.0
	 *
	 * @param WP_Post $post       The post object.
	 * @param array   $purge_urls URLs cache files to remove.
	 * @param string  $lang       The post language.
	 *
	 * @return mixed Object|bool true if the purge is successful, WP_Error otherwise
	 */
	public function purge_by_url( $post, $purge_urls, $lang ) {
		if ( is_wp_error( $this->cloudflare_api_error ) ) {
			return $this->cloudflare_api_error;
		}

		try {
			$cf_purge = $this->api->purge_files( $purge_urls );

			if ( empty( $cf_purge->success ) ) {
				foreach ( $cf_purge->errors as $error ) {
					$errors[] = $error->message;
				}

				$errors = wp_sprintf_l( '%l ', $errors );

				return new WP_Error( 'cloudflare_purge_failed', $errors );
			}

			return true;

		} catch ( Exception $e ) {
			return new WP_Error( 'cloudflare_purge_failed', $e->getMessage() );
		}
	}

	/**
	 * Set the Browser Cache TTL in Cloudflare.
	 *
	 * @since 1.0
	 *
	 * @param string $mode Value for Cloudflare browser cache TTL.
	 *
	 * @return mixed Object|String Mode value if the update is successful, WP_Error otherwise.
	 */
	public function set_browser_cache_ttl( $mode ) {
		if ( is_wp_error( $this->cloudflare_api_error ) ) {
			return $this->cloudflare_api_error;
		}

		try {
			$cf_return = $this->api->change_browser_cache_ttl( (int) $mode );

			if ( empty( $cf_return->success ) ) {
				foreach ( $cf_return->errors as $error ) {
					$errors[] = $error->message;
				}

				$errors = wp_sprintf_l( '%l ', $errors );

				return new WP_Error( 'cloudflare_browser_cache', $errors );
			}

			return $mode;
		} catch ( Exception $e ) {
			return new WP_Error( 'cloudflare_browser_cache', $e->getMessage() );
		}
	}

	/**
	 * Set the Cloudflare Rocket Loader.
	 *
	 * @since 1.0
	 *
	 * @param string $mode Value for Cloudflare Rocket Loader.
	 *
	 * @return mixed Object|String Mode value if the update is successful, WP_Error otherwise.
	 */
	public function set_rocket_loader( $mode ) {
		if ( is_wp_error( $this->cloudflare_api_error ) ) {
			return $this->cloudflare_api_error;
		}

		try {
			$cf_return = $this->api->change_rocket_loader( $mode );

			if ( empty( $cf_return->success ) ) {
				foreach ( $cf_return->errors as $error ) {
					$errors[] = $error->message;
				}

				$errors = wp_sprintf_l( '%l ', $errors );

				return new WP_Error( 'cloudflare_rocket_loader', $errors );
			}

			return $mode;
		} catch ( Exception $e ) {
			return new WP_Error( 'cloudflare_rocket_loader', $e->getMessage() );
		}
	}

	/**
	 * Set the Cloudflare Minification.
	 *
	 * @since 1.0
	 *
	 * @param string $mode Value for Cloudflare minification.
	 *
	 * @return mixed Object|String Mode value if the update is successful, WP_Error otherwise.
	 */
	public function set_minify( $mode ) {
		if ( is_wp_error( $this->cloudflare_api_error ) ) {
			return $this->cloudflare_api_error;
		}

		$cf_minify_settings = [
			'css'  => $mode,
			'html' => $mode,
			'js'   => $mode,
		];

		try {
			$cf_return = $this->api->change_minify( $cf_minify_settings );

			if ( empty( $cf_return->success ) ) {
				foreach ( $cf_return->errors as $error ) {
					$errors[] = $error->message;
				}

				$errors = wp_sprintf_l( '%l ', $errors );

				return new WP_Error( 'cloudflare_minification', $errors );
			}

			return $mode;
		} catch ( Exception $e ) {
			return new WP_Error( 'cloudflare_minification', $e->getMessage() );
		}
	}

	/**
	 * Set the Cloudflare Caching level.
	 *
	 * @since 1.0
	 *
	 * @param string $mode Value for Cloudflare caching level.
	 *
	 * @return mixed Object|String Mode value if the update is successful, WP_Error otherwise.
	 */
	public function set_cache_level( $mode ) {
		if ( is_wp_error( $this->cloudflare_api_error ) ) {
			return $this->cloudflare_api_error;
		}

		try {
			$cf_return = $this->api->change_cache_level( $mode );

			if ( empty( $cf_return->success ) ) {
				foreach ( $cf_return->errors as $error ) {
					$errors[] = $error->message;
				}

				$errors = wp_sprintf_l( '%l ', $errors );

				return new WP_Error( 'cloudflare_cache_level', $errors );
			}

			return $mode;
		} catch ( Exception $e ) {
			return new WP_Error( 'cloudflare_cache_level', $e->getMessage() );
		}
	}

	/**
	 * Set the Cloudflare Development mode.
	 *
	 * @since 1.0
	 *
	 * @param string $mode Value for Cloudflare development mode.
	 *
	 * @return mixed Object|String Mode value if the update is successful, WP_Error otherwise.
	 */
	public function set_devmode( $mode ) {
		if ( is_wp_error( $this->cloudflare_api_error ) ) {
			return $this->cloudflare_api_error;
		}

		if ( 0 === (int) $mode ) {
			$value = 'off';
		} else {
			$value = 'on';
		}

		try {
			$cf_return = $this->api->change_development_mode( $value );

			if ( empty( $cf_return->success ) ) {
				foreach ( $cf_return->errors as $error ) {
					$errors[] = $error->message;
				}

				$errors = wp_sprintf_l( '%l ', $errors );

				return new WP_Error( 'cloudflare_dev_mode', $errors );
			}

			if ( 'on' === $value ) {
				wp_schedule_single_event( time() + 3 * HOUR_IN_SECONDS, 'rocket_cron_deactivate_cloudflare_devmode' );
			}

			return $value;
		} catch ( Exception $e ) {
			return new WP_Error( 'cloudflare_dev_mode', $e->getMessage() );
		}
	}

	/**
	 * Get all the current Cloudflare settings for a given domain.
	 *
	 * @since 1.0
	 *
	 * @return mixed bool|Array Array of Cloudflare settings, false if any error connection to Cloudflare.
	 */
	public function get_settings() {
		if ( is_wp_error( $this->cloudflare_api_error ) ) {
			return $this->cloudflare_api_error;
		}

		try {
			$cf_settings = $this->api->get_settings();

			if ( empty( $cf_settings->success ) ) {
				foreach ( $cf_settings->errors as $error ) {
					$errors[] = $error->message;
				}

				$errors = wp_sprintf_l( '%l ', $errors );

				return new WP_Error( 'cloudflare_dev_mode', $errors );
			}

			foreach ( $cf_settings->result as $cloudflare_option ) {
				switch ( $cloudflare_option->id ) {
					case 'browser_cache_ttl':
						$browser_cache_ttl = $cloudflare_option->value;
						break;
					case 'cache_level':
						$cache_level = $cloudflare_option->value;
						break;
					case 'rocket_loader':
						$rocket_loader = $cloudflare_option->value;
						break;
					case 'minify':
						$cf_minify = $cloudflare_option->value;
						break;
				}
			}
			$cf_minify_value = 'on';

			if ( 'off' === $cf_minify->js || 'off' === $cf_minify->css || 'off' === $cf_minify->html ) {
				$cf_minify_value = 'off';
			}

			$cf_settings_array = [
				'cache_level'       => $cache_level,
				'minify'            => $cf_minify_value,
				'rocket_loader'     => $rocket_loader,
				'browser_cache_ttl' => $browser_cache_ttl,
			];

			return $cf_settings_array;
		} catch ( Exception $e ) {
			return new WP_Error( 'cloudflare_current_settings', $e->getMessage() );
		}
	}

	/**
	 * Get Cloudflare IPs. No API validation needed, all exceptions returns the default CF IPs array.
	 *
	 * @since 1.0
	 *
	 * @return Object Result of API request if successful, default CF IPs otherwise.
	 */
	public function get_cloudflare_ips() {
		$cf_ips = get_transient( 'rocket_cloudflare_ips' );
		if ( false !== $cf_ips ) {
			return $cf_ips;
		}

		try {
			$cf_ips = $this->api->get_ips();

			if ( empty( $cf_ips->success ) ) {
				// Set default IPs from Cloudflare if call to Cloudflare /ips API does not contain a success.
				// Prevents from making API calls on each page load.
				$cf_ips = $this->get_default_ips();
			}
		} catch ( Exception $e ) {
			// Set default IPs from Cloudflare if call to Cloudflare /ips API fails.
			// Prevents from making API calls on each page load.
			$cf_ips = $this->get_default_ips();
		}

		set_transient( 'rocket_cloudflare_ips', $cf_ips, 2 * WEEK_IN_SECONDS );

		return $cf_ips;
	}

	/**
	 * Get default Cloudflare IPs.
	 *
	 * @since 1.0
	 *
	 * @return stdClass Default Cloudflare connecting IPs.
	 */
	private function get_default_ips() {
		$cf_ips = (object) [
			'result'   => (object) [],
			'success'  => true,
			'errors'   => [],
			'messages' => [],
		];

		$cf_ips->result->ipv4_cidrs = [
			'173.245.48.0/20',
			'103.21.244.0/22',
			'103.22.200.0/22',
			'103.31.4.0/22',
			'141.101.64.0/18',
			'108.162.192.0/18',
			'190.93.240.0/20',
			'188.114.96.0/20',
			'197.234.240.0/22',
			'198.41.128.0/17',
			'162.158.0.0/15',
			'104.16.0.0/12',
			'172.64.0.0/13',
			'131.0.72.0/22',
		];

		$cf_ips->result->ipv6_cidrs = [
			'2400:cb00::/32',
			'2606:4700::/32',
			'2803:f800::/32',
			'2405:b500::/32',
			'2405:8100::/32',
			'2a06:98c0::/29',
			'2c0f:f248::/32',
		];

		return $cf_ips;
	}
}
