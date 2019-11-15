<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Validate Cloudflare input data
 *
 * @since 3.4.1
 * @deprecated 3.4.2
 * @author Soponar Cristina
 *
 * @param string $cf_email         - Cloudflare email.
 * @param string $cf_api_key       - Cloudflare API key.
 * @param string $cf_zone_id       - Cloudflare zone ID.
 * @param bool   $basic_validation - Bypass Cloudflare API user and zone validation.
 * @return Object                  - true if credentials are ok, WP_Error otherwise.
 */
function rocket_is_api_keys_valid_cloudflare( $cf_email, $cf_api_key, $cf_zone_id, $basic_validation = true ) {
	_deprecated_function( __FUNCTION__ . '()', '3.4.2', '\WP_Rocket\Subscriber\Tools\Cloudflare_Subscriber::is_api_keys_valid()' );
	if ( ! function_exists( 'curl_init' ) || ! function_exists( 'curl_exec' ) ) {
		return new WP_Error( 'curl_disabled', __( 'Curl is disabled on your server. Please ask your host to enable it. This is required for the Cloudflare Add-on to work correctly.', 'rocket' ) );
	}

	if ( ! isset( $cf_email, $cf_api_key ) || empty( $cf_email ) || empty( $cf_api_key ) ) {
		return new WP_Error(
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

		return new WP_Error( 'cloudflare_no_zone_id', $msg );
	}

	if ( $basic_validation ) {
		return true;
	}

	try {
		$cf_api_instance = new Cloudflare\Api( $cf_email, $cf_api_key );
		$cf_zone         = $cf_api_instance->get( 'zones/' . $cf_zone_id );

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

				return new WP_Error( 'cloudflare_wrong_zone_id', $msg );
			}

			return true;
		}

	} catch ( Exception $e ) {
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

/**
 * Get a Cloudflare\Api instance
 *
 * @since 2.8.21
 * @deprecated 3.4.2
 * @author Soponar Cristina
 *
 * @return Object Cloudflare\Api instance if crendentials are set, WP_Error otherwise
 */
function get_rocket_cloudflare_api_instance() {
	_deprecated_function( __FUNCTION__ . '()', '3.4.2' );
	if ( ! function_exists( 'curl_init' ) || ! function_exists( 'curl_exec' ) ) {
		return new WP_Error( 'curl_disabled', __( 'Curl is disabled on your server. Please ask your host to enable it. This is required for the Cloudflare Add-on to work correctly.', 'rocket' ) );
	}

	$cf_email   = get_rocket_option( 'cloudflare_email', null );
	$cf_api_key = ( defined( 'WP_ROCKET_CF_API_KEY' ) ) ? WP_ROCKET_CF_API_KEY : get_rocket_option( 'cloudflare_api_key', null );

	if ( ! isset( $cf_email, $cf_api_key ) ) {
		return new WP_Error(
			'cloudflare_credentials_empty',
			sprintf(
				/* translators: %1$s = opening link; %2$s = closing link */
				__( 'Cloudflare email and API key are not set. Read the %1$sdocumentation%2$s for further guidance.', 'rocket' ),
				// translators: Documentation exists in EN, FR; use localized URL if applicable.
				'<a href="' . esc_url( __( 'https://docs.wp-rocket.me/article/18-using-wp-rocket-with-cloudflare/?utm_source=wp_plugin&utm_medium=wp_rocket#add-on', 'rocket' ) ) . '" rel="noopener noreferrer" target="_blank">',
				'</a>'
			)
		);
	}
	return new Cloudflare\Api( $cf_email, $cf_api_key );
}

/**
 * Get a Cloudflare\Api instance & the zone_id corresponding to the domain
 *
 * @since 2.8.21 Get the zone ID from the options
 * @since 2.8.18 Add try/catch to prevent fatal error Uncaugh Exception
 * @since 2.8.16 Update to Cloudflare API v4
 * @since 2.5
 * @deprecated 3.4.2
 *
 * @return Object Cloudflare instance & zone_id if credentials are correct, WP_Error otherwise
 */
function get_rocket_cloudflare_instance() {
	_deprecated_function( __FUNCTION__ . '()', '3.4.2', '\WP_Rocket\Subscriber\Tools\Cloudflare_Subscriber::get_instance()' );
	$cf_email             = get_rocket_option( 'cloudflare_email', null );
	$cf_api_key           = ( defined( 'WP_ROCKET_CF_API_KEY' ) ) ? WP_ROCKET_CF_API_KEY : get_rocket_option( 'cloudflare_api_key', null );
	$cf_zone_id           = get_rocket_option( 'cloudflare_zone_id', null );
	$is_api_keys_valid_cf = rocket_is_api_keys_valid_cloudflare( $cf_email, $cf_api_key, $cf_zone_id, true );

	if ( is_wp_error( $is_api_keys_valid_cf ) ) {
		return $is_api_keys_valid_cf;
	}

	$cf_api_instance = get_rocket_cloudflare_api_instance();

	$cf_instance = (object) [
		'auth'    => $cf_api_instance,
		'zone_id' => $cf_zone_id,
	];

	return $cf_instance;
}


/**
 * Test the connection with Cloudflare
 *
 * @since 2.9
 * @deprecated 3.4.2
 * @author Remy Perona
 *
 * @throws Exception If the connection to Cloudflare failed.
 * @return Object True if connection is successful, WP_Error otherwise
 */
function rocket_cloudflare_valid_auth() {
	_deprecated_function( __FUNCTION__ . '()', '3.4.2' );
	$cf_api_instance = get_rocket_cloudflare_api_instance();
	if ( is_wp_error( $cf_api_instance ) ) {
		return $cf_api_instance;
	}

	try {
		$cf_zone_instance = new Cloudflare\Zone( $cf_api_instance );
		$cf_zones         = $cf_zone_instance->zones();

		if ( ! isset( $cf_zones->success ) || empty( $cf_zones->success ) ) {
			throw new Exception( __( 'Connection to Cloudflare failed', 'rocket' ) );
		}

		if ( true === $cf_zones->success ) {
			return true;
		}
	} catch ( Exception $e ) {
		return new WP_Error( 'cloudflare_invalid_auth', $e->getMessage() );
	}
}



/**
 * Get all the current Cloudflare settings for a given domain.
 *
 * @since 2.8.16 Update to Cloudflare API v4
 * @since 2.5
 * @deprecated 3.4.2
 *
 * @return mixed bool|Array Array of Cloudflare settings, false if any error connection to Cloudflare
 */
function get_rocket_cloudflare_settings() {
	_deprecated_function( __FUNCTION__ . '()', '3.4.2', '\WP_Rocket\Subscriber\Tools\Cloudflare_Subscriber::get_settings()' );
	if ( is_wp_error( $GLOBALS['rocket_cloudflare'] ) ) {
		return $GLOBALS['rocket_cloudflare'];
	}

	try {
		$cf_settings_instance = new Cloudflare\Zone\Settings( $GLOBALS['rocket_cloudflare']->auth );
		$cf_settings          = $cf_settings_instance->settings( $GLOBALS['rocket_cloudflare']->zone_id );
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
	} catch ( Exception $e ) {
		return new WP_Error( 'cloudflare_current_settings', $e->getMessage() );
	}
}


/**
 * Set the Cloudflare Development mode.
 *
 * @since 2.9 Now returns a value
 * @since 2.8.16 Update to Cloudflare API v4
 * @since 2.5
 * @deprecated 3.4.2
 *
 * @param string $mode Value for Cloudflare development mode.
 * @throws Exception If any error occurs when doing the API request.
 * @return mixed Object|String Mode value if the update is successful, WP_Error otherwise
 */
function set_rocket_cloudflare_devmode( $mode ) {
	_deprecated_function( __FUNCTION__ . '()', '3.4.2', '\WP_Rocket\Subscriber\Tools\Cloudflare_Subscriber::set_devmode()' );
	if ( is_wp_error( $GLOBALS['rocket_cloudflare'] ) ) {
		return $GLOBALS['rocket_cloudflare'];
	}

	if ( (int) 0 === $mode ) {
		$value = 'off';
	} elseif ( (int) 1 === $mode ) {
		$value = 'on';
	}

	try {
		$cf_settings = new Cloudflare\Zone\Settings( $GLOBALS['rocket_cloudflare']->auth );
		$cf_return   = $cf_settings->change_development_mode( $GLOBALS['rocket_cloudflare']->zone_id, $value );

		if ( ! isset( $cf_return->success ) || empty( $cf_return->success ) ) {
			foreach ( $cf_return->errors as $error ) {
				$errors[] = $error->message;
			}

			$errors = implode( ', ', $errors );
			throw new Exception( $errors );
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
 * Set the Cloudflare Caching level.
 *
 * @since 2.9 Now returns a value
 * @since 2.8.16 Update to Cloudflare API v4
 * @since 2.5
 * @deprecated 3.4.2
 *
 * @param string $mode Value for Cloudflare caching level.
 * @throws Exception If any error occurs when doing the API request.
 * @return mixed Object|String Mode value if the update is successful, WP_Error otherwise
 */
function set_rocket_cloudflare_cache_level( $mode ) {
	_deprecated_function( __FUNCTION__ . '()', '3.4.2', '\WP_Rocket\Subscriber\Tools\Cloudflare_Subscriber::set_cache_level()' );
	if ( is_wp_error( $GLOBALS['rocket_cloudflare'] ) ) {
		return $GLOBALS['rocket_cloudflare'];
	}

	try {
		$cf_settings = new Cloudflare\Zone\Settings( $GLOBALS['rocket_cloudflare']->auth );
		$cf_return   = $cf_settings->change_cache_level( $GLOBALS['rocket_cloudflare']->zone_id, $mode );

		if ( ! isset( $cf_return->success ) || empty( $cf_return->success ) ) {
			foreach ( $cf_return->errors as $error ) {
				$errors[] = $error->message;
			}

			$errors = implode( ', ', $errors );
			throw new Exception( $errors );
		}

		return $mode;
	} catch ( Exception $e ) {
		return new WP_Error( 'cloudflare_cache_level', $e->getMessage() );
	}
}

/**
 * Set the Cloudflare Minification.
 *
 * @since 2.9 Now returns a value
 * @since 2.8.16 Update to Cloudflare API v4
 * @since 2.5
 * @deprecated 3.4.2
 *
 * @param string $mode Value for Cloudflare minification.
 * @throws Exception If any error occurs when doing the API request.
 * @return mixed Object|String Mode value if the update is successful, WP_Error otherwise
 */
function set_rocket_cloudflare_minify( $mode ) {
	_deprecated_function( __FUNCTION__ . '()', '3.4.2', '\WP_Rocket\Subscriber\Tools\Cloudflare_Subscriber::set_minify()' );
	if ( is_wp_error( $GLOBALS['rocket_cloudflare'] ) ) {
		return $GLOBALS['rocket_cloudflare'];
	}

	$cf_minify_settings = array(
		'css'  => $mode,
		'html' => $mode,
		'js'   => $mode,
	);

	try {
		$cf_settings = new Cloudflare\Zone\Settings( $GLOBALS['rocket_cloudflare']->auth );
		$cf_return   = $cf_settings->change_minify( $GLOBALS['rocket_cloudflare']->zone_id, $cf_minify_settings );

		if ( ! isset( $cf_return->success ) || empty( $cf_return->success ) ) {
			foreach ( $cf_return->errors as $error ) {
				$errors[] = $error->message;
			}

			$errors = implode( ', ', $errors );
			throw new Exception( $errors );
		}

		return $mode;
	} catch ( Exception $e ) {
		return new WP_Error( 'cloudflare_minification', $e->getMessage() );
	}
}


/**
 * Set the Cloudflare Rocket Loader.
 *
 * @since 2.9 Now returns value
 * @since 2.8.16 Update to Cloudflare API v4
 * @since 2.5
 * @deprecated 3.4.2
 *
 * @param string $mode Value for Cloudflare Rocket Loader.
 * @throws Exception If any error occurs when doing the API request.
 * @return mixed Object|String Mode value if the update is successful, WP_Error otherwise
 */
function set_rocket_cloudflare_rocket_loader( $mode ) {
	_deprecated_function( __FUNCTION__ . '()', '3.4.2', '\WP_Rocket\Subscriber\Tools\Cloudflare_Subscriber::set_rocket_loader()' );
	if ( is_wp_error( $GLOBALS['rocket_cloudflare'] ) ) {
		return $GLOBALS['rocket_cloudflare'];
	}

	try {
		$cf_settings = new Cloudflare\Zone\Settings( $GLOBALS['rocket_cloudflare']->auth );
		$cf_return   = $cf_settings->change_rocket_loader( $GLOBALS['rocket_cloudflare']->zone_id, $mode );

		if ( ! isset( $cf_return->success ) || empty( $cf_return->success ) ) {
			foreach ( $cf_return->errors as $error ) {
				$errors[] = $error->message;
			}

			$errors = implode( ', ', $errors );
			throw new Exception( $errors );
		}

		return $mode;
	} catch ( Exception $e ) {
		return new WP_Error( 'cloudflare_rocket_loader', $e->getMessage() );
	}
}


/**
 * Set the Browser Cache TTL in Cloudflare.
 *
 * @since 2.9 Now returns value
 * @since 2.8.16
 * @deprecated 3.4.2
 *
 * @param string $mode Value for Cloudflare browser cache TTL.
 * @throws Exception If any error occurs when doing the API request.
 * @return mixed Object|String Mode value if the update is successful, WP_Error otherwise
 */
function set_rocket_cloudflare_browser_cache_ttl( $mode ) {
	_deprecated_function( __FUNCTION__ . '()', '3.4.2', '\WP_Rocket\Subscriber\Tools\Cloudflare_Subscriber::set_browser_cache_ttl()' );
	if ( is_wp_error( $GLOBALS['rocket_cloudflare'] ) ) {
		return $GLOBALS['rocket_cloudflare'];
	}

	try {
		$cf_settings = new Cloudflare\Zone\Settings( $GLOBALS['rocket_cloudflare']->auth );
		$cf_return   = $cf_settings->change_browser_cache_ttl( $GLOBALS['rocket_cloudflare']->zone_id, (int) $mode );

		if ( ! isset( $cf_return->success ) || empty( $cf_return->success ) ) {
			foreach ( $cf_return->errors as $error ) {
				$errors[] = $error->message;
			}

			$errors = implode( ', ', $errors );
			throw new Exception( $errors );
		}

		return $mode;
	} catch ( Exception $e ) {
		return new WP_Error( 'cloudflare_browser_cache', $e->getMessage() );
	}
}


/**
 * Purge Cloudflare cache.
 *
 * @since 2.9 Now returns value
 * @since 2.8.16 Update to Cloudflare API v4
 * @since 2.5
 * @deprecated 3.4.2
 *
 * @throws Exception If any error occurs when doing the API request.
 * @return mixed Object|bool true if the purge is successful, WP_Error otherwise
 */
function rocket_purge_cloudflare() {
	_deprecated_function( __FUNCTION__ . '()', '3.4.2', '\WP_Rocket\Subscriber\Tools\Cloudflare_Subscriber::purge_cloudflare()' );
	if ( is_wp_error( $GLOBALS['rocket_cloudflare'] ) ) {
		return $GLOBALS['rocket_cloudflare'];
	}

	try {
		$cf_cache = new Cloudflare\Zone\Cache( $GLOBALS['rocket_cloudflare']->auth );
		$cf_purge = $cf_cache->purge( $GLOBALS['rocket_cloudflare']->zone_id, true );

		if ( ! isset( $cf_purge->success ) || empty( $cf_purge->success ) ) {
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

		return true;

	} catch ( Exception $e ) {
		$msg = __( 'Incorrect Cloudflare email address or API key.', 'rocket' );

		$msg .= ' ' . sprintf(
			/* translators: %1$s = opening link; %2$s = closing link */
			__( 'Read the %1$sdocumentation%2$s for further guidance.', 'rocket' ),
			// translators: Documentation exists in EN, FR; use localized URL if applicable.
			'<a href="' . esc_url( __( 'https://docs.wp-rocket.me/article/18-using-wp-rocket-with-cloudflare/?utm_source=wp_plugin&utm_medium=wp_rocket#add-on', 'rocket' ) ) . '" rel="noopener noreferrer" target="_blank">',
			'</a>'
		);
		return new WP_Error( 'cloudflare_purge_failed', $msg );
	}
}

/**
 * Get Cloudflare IPs.
 *
 * @since 2.8.21 Save IPs in a transient to prevent calling the API everytime
 * @since 2.8.16
 * @deprecated 3.4.2
 *
 * @author Remy Perona
 *
 * @throws Exception If any error occurs when doing the API request.
 * @return Object Result of API request if successful, WP_Error otherwise
 */
function rocket_get_cloudflare_ips() {
	_deprecated_function( __FUNCTION__ . '()', '3.4.2', '\WP_Rocket\Subscriber\Tools\Cloudflare_Subscriber::get_cloudflare_ips()' );
	$cf_instance = get_rocket_cloudflare_api_instance();
	if ( is_wp_error( $cf_instance ) ) {
		return $cf_instance;
	}

	$cf_ips = get_transient( 'rocket_cloudflare_ips' );
	if ( false === $cf_ips ) {
		try {
			$cf_ips_instance = new Cloudflare\IPs( $cf_instance );
			$cf_ips          = $cf_ips_instance->ips();

			if ( ! isset( $cf_ips->success ) || ! $cf_ips->success ) {
				throw new Exception( 'Error connecting to Cloudflare' );
			}

			set_transient( 'rocket_cloudflare_ips', $cf_ips, 2 * WEEK_IN_SECONDS );
		} catch ( Exception $e ) {
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

			set_transient( 'rocket_cloudflare_ips', $cf_ips, 2 * WEEK_IN_SECONDS );
			return $cf_ips;
		}
	}

	return $cf_ips;
}

/**
 * Set Real IP from CloudFlare
 *
 * @since 2.8.16 Uses CloudFlare API v4 to get CloudFlare IPs
 * @since 2.5.4
 *
 * @deprecated 3.4.2
 *
 * @source cloudflare.php - https://wordpress.org/plugins/cloudflare/
 */
function rocket_set_real_ip_cloudflare() {
	_deprecated_function( __FUNCTION__ . '()', '3.4.2', '\WP_Rocket\Subscriber\Tools\Cloudflare_Subscriber::set_real_ip()' );
	global $is_cf;

	$is_cf = ( isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) ? true : false;

	if ( ! $is_cf ) {
		return;
	}

	// only run this logic if the REMOTE_ADDR is populated, to avoid causing notices in CLI mode.
	if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
		$cf_ips_values = rocket_get_cloudflare_ips();

		if ( is_wp_error( $cf_ips_values ) || ! isset( $cf_ips_values->success ) || ! $cf_ips_values->success ) {
			return;
		}

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
		}
		else {
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
 * This notice is displayed after purging the CloudFlare cache
 *
 * @since 2.9
 * @author Remy Perona
 *
 * @deprecated 3.4.2
 *
 */
function rocket_cloudflare_purge_result() {
	_deprecated_function( __FUNCTION__ . '()', '3.4.2', '\WP_Rocket\Subscriber\Tools\Cloudflare_Subscriber::maybe_print_notice()' );
	global $current_user;
	if ( ! current_user_can( 'rocket_purge_cloudflare_cache' ) ) {
		return;
	}

	if ( ! is_admin() ) {
		return;
	}

	$notice = get_transient( $current_user->ID . '_cloudflare_purge_result' );
	if ( ! $notice ) {
		return;
	}

	delete_transient( $current_user->ID . '_cloudflare_purge_result' );

	rocket_notice_html( [
		'status'  => $notice['result'],
		'message' => $notice['message'],
	 ] );
}

/**
 * Purge CloudFlare cache
 *
 * @since 2.5
 *
 * @deprecated 3.4.2
 *
 */
function do_admin_post_rocket_purge_cloudflare() {
	_deprecated_function( __FUNCTION__ . '()', '3.4.2', '\WP_Rocket\Subscriber\Tools\Cloudflare_Subscriber::do_purge_cloudflare()' );
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'rocket_purge_cloudflare' ) ) {
		wp_nonce_ays( '' );
	}

	if ( ! current_user_can( 'rocket_purge_cloudflare_cache' ) ) {
		return;
	}

	// Purge CloudFlare.
	$cf_purge = rocket_purge_cloudflare();

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
 * This notice is displayed after modifying the CloudFlare settings
 *
 * @since 2.9
 * @author Remy Perona
 *
 * @deprecated 3.4.2
 */
function rocket_cloudflare_update_settings() {
	_deprecated_function( __FUNCTION__ . '()', '3.4.2', '\WP_Rocket\Subscriber\Tools\Cloudflare_Subscriber::maybe_print_update_settings_notice()' );
	global $current_user;
	$screen = get_current_screen();

	if ( ! current_user_can( 'rocket_manage_options' ) ) {
		return;
	}

	if ( 'settings_page_wprocket' !== $screen->id ) {
		return;
	}

	$notices = get_transient( $current_user->ID . '_cloudflare_update_settings' );
	if ( $notices ) {
		$errors  = '';
		$success = '';
		delete_transient( $current_user->ID . '_cloudflare_update_settings' );
		foreach ( $notices as $notice ) {
			if ( 'error' === $notice['result'] ) {
				$errors .= $notice['message'] . '<br>';
			} elseif ( 'success' === $notice['result'] ) {
				$success .= $notice['message'] . '<br>';
			}
		}

		if ( ! empty( $success ) ) {
			rocket_notice_html( [
				'message' => $success,
			 ] );
		}

		if ( ! empty( $errors ) ) {
			rocket_notice_html( [
				'status'  => 'error',
				'message' => $success,
			 ] );
		}
	}
}

/**
 * Get Zones linked to a Cloudflare account
 *
 * @since 2.9
 * @deprecated 3.4.1.2
 * @author Remy Perona
 *
 * @return Array List of zones or default no domain
 */
function get_rocket_cloudflare_zones() {
	_deprecated_function( __FUNCTION__ . '()', '3.4.1.2' );
	$cf_api_instance = get_rocket_cloudflare_api_instance();
	$domains         = array(
		'' => __( 'Choose a domain from the list', 'rocket' ),
	);

	if ( is_wp_error( $cf_api_instance ) ) {
		return $domains;
	}

	try {
		$cf_zone_instance = new Cloudflare\Zone( $cf_api_instance );
		$cf_zones         = $cf_zone_instance->zones( null, 'active', null, 50 );
		$cf_zones_list    = $cf_zones->result;

		if ( ! (bool) $cf_zones_list ) {
			$domains[] = __( 'No domain available in your Cloudflare account', 'rocket' );

			return $domains;
		}

		foreach ( $cf_zones_list as $cf_zone ) {
			$domains[ $cf_zone->name ] = $cf_zone->name;
		}

		return $domains;
	} catch ( Exception $e ) {
		return $domains;
	}
}


/**
 * Get CNAMES hosts
 *
 * @since 2.3
 * @deprecated 3.4
 *
 * @param  string $zones CNAMES zones.
 * @return array $hosts CNAMES hosts
 */
function get_rocket_cnames_host( $zones = array( 'all' ) ) {
	_deprecated_function( __FUNCTION__ . '()', '3.4', '\WP_Rocket\Subscriber\CDN\CDNSubscriber::get_cdn_hosts()' );
	$hosts = array();

	$cnames = get_rocket_cdn_cnames( $zones );
	if ( $cnames ) {
		foreach ( $cnames as $cname ) {
			$cname   = rocket_add_url_protocol( $cname );
			$hosts[] = rocket_extract_url_component( $cname, PHP_URL_HOST );
		}
	}

	return $hosts;
}

/**
 * Apply CDN on CSS properties (background, background-image, @import, src:url (fonts))
 *
 * @since 2.6
 * @since 3.4
 *
 * @param  string $buffer file content.
 * @return string modified file content
 */
function rocket_cdn_css_properties( $buffer ) {
	_deprecated_function( __FUNCTION__ . '()', '3.4', '\WP_Rocket\Subscriber\CDN\CDN::rewrite_css_properties()' );

	$zone   = array(
		'all',
		'images',
		'css_and_js',
		'css',
	);
	$cnames = get_rocket_cdn_cnames( $zone );

	/**
	 * Filters the application of the CDN on CSS properties
	 *
	 * @since 2.6
	 *
	 * @param bool true to apply CDN to properties, false otherwise
	 */
	$do_rocket_cdn_css_properties = apply_filters( 'do_rocket_cdn_css_properties', true );

	if ( ! get_rocket_option( 'cdn' ) || ! $cnames || ! $do_rocket_cdn_css_properties ) {
		return $buffer;
	}

	preg_match_all( '/url\((?![\'"]?data)([^\)]+)\)/i', $buffer, $matches );

	if ( is_array( $matches ) ) {
		$i = 0;
		foreach ( $matches[1] as $url ) {
			$url = trim( $url, " \t\n\r\0\x0B\"'" );
			/**
			 * Filters the URL of the CSS property
			 *
			 * @since 2.8
			 *
			 * @param string $url URL of the CSS property
			 */
			$url      = get_rocket_cdn_url( apply_filters( 'rocket_cdn_css_properties_url', $url ), $zone );
			$property = str_replace( $matches[1][ $i ], $url, $matches[0][ $i ] );
			$buffer   = str_replace( $matches[0][ $i ], $property, $buffer );

			$i++;
		}
	}

	return $buffer;
}

/**
 * Apply CDN on custom data attributes.
 *
 * @since 2.5.5
 * @deprecated 3.4
 *
 * @param   string $html Original Output.
 * @return  string $html Output that will be printed
 */
function rocket_add_cdn_on_custom_attr( $html ) {
	_deprecated_function( __FUNCTION__ . '()', '3.4' );
	if ( preg_match( '/(data-lazy-src|data-lazyload|data-src|data-retina)=[\'"]?([^\'"\s>]+)[\'"]/i', $html, $matches ) ) {
		$html = str_replace( $matches[2], get_rocket_cdn_url( $matches[2], array( 'all', 'images' ) ), $html );
	}

	return $html;
}


/**
 * Replace URL by CDN of all thumbnails and smilies.
 *
 * @since 2.1
 * @deprecated 3.4
 *
 * @param string $url URL of the file to replace the domain with the CDN.
 * @return string modified URL
 */
function rocket_cdn_file( $url ) {
	_deprecated_function( __FUNCTION__ . '()', '3.4' );
	if ( defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) {
		return $url;
	}

	if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
		return $url;
	}

	$ext = pathinfo( $url, PATHINFO_EXTENSION );

	if ( is_admin() || 'php' === $ext ) {
		return $url;
	}

	$filter = current_filter();

	$rejected_files = get_rocket_cdn_reject_files();
	if ( 'template_directory_uri' === $filter && ! empty( $rejected_files ) ) {
		return $url;
	}

	switch ( $filter ) {
		case 'wp_get_attachment_url':
		case 'wp_calculate_image_srcset':
			$zone = array( 'all', 'images' );
			break;
		case 'smilies_src':
			$zone = array( 'all', 'images' );
			break;
		case 'stylesheet_uri':
		case 'wp_minify_css_url':
		case 'wp_minify_js_url':
		case 'bwp_get_minify_src':
			$zone = array( 'all', 'css_and_js', $ext );
			break;
		default:
			$zone = array( 'all', $ext );
			break;
	}

	$cnames = get_rocket_cdn_cnames( $zone );
	if ( $cnames ) {
		$url = get_rocket_cdn_url( $url, $zone );
	}

	return $url;
}

/**
 * Replace URL by CDN of images displayed using wp_get_attachment_image_src
 *
 * @since 2.9.2
 * @deprecated 3.4
 * @author Remy Perona
 * @source https://github.com/wp-media/wp-rocket/issues/271#issuecomment-269849927
 *
 * @param array $image An array containing the src, width and height of the image.
 * @return array Array with updated src URL
 */
function rocket_cdn_attachment_image_src( $image ) {
	_deprecated_function( __FUNCTION__ . '()', '3.4' );
	if ( defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) {
		return $image;
	}

	if ( ! (bool) $image ) {
		return $image;
	}

	if ( is_admin() || is_preview() || is_feed() ) {
		return $image;
	}

	$zones = array( 'all', 'images' );

	if ( ! (bool) get_rocket_cdn_cnames( $zones ) ) {
		return $image;
	}

	$image[0] = get_rocket_cdn_url( $image[0], $zones );

	return $image;
}

/**
 * Replace srcset URLs by CDN URLs for WP responsive images
 *
 * @since WP 4.4
 * @since 2.6.14
 * @deprecated 3.4
 * @author Remy Perona
 *
 * @param  array $sources multidimensional array containing srcset images urls.
 * @return array $sources
 */
function rocket_add_cdn_on_srcset( $sources ) {
	_deprecated_function( __FUNCTION__ . '()', '3.4' );
	if ( defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) {
		return $sources;
	}

	if ( (bool) $sources ) {
		foreach ( $sources as $width => $data ) {
			$sources[ $width ]['url'] = rocket_cdn_file( $data['url'] );
		}
	}
	return $sources;
}

/**
 * Replace URL by CDN of all images display in a post content or a widget text.
 *
 * @since 2.1
 * @deprecated 3.4
 *
 * @param  string $html HTML content to parse.
 * @return string modified HTML content
 */
function rocket_cdn_images( $html ) {
	_deprecated_function( __FUNCTION__ . '()', '3.4' );
	// Don't use CDN if the image is in admin, a feed or in a post preview.
	if ( is_admin() || is_feed() || is_preview() || empty( $html ) || defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) {
		return $html;
	}

	$zone   = array( 'all', 'images' );
	$cnames = get_rocket_cdn_cnames( $zone );

	if ( $cnames ) {

		$cnames             = array_flip( $cnames );
		$wp_content_dirname = wp_parse_url( content_url(), PHP_URL_PATH );

		$custom_media_uploads_dirname = '';
		$uploads_info                 = wp_upload_dir();

		if ( ! empty( $uploads_info['baseurl'] ) ) {
			$custom_media_uploads_dirname = '|' . trailingslashit( wp_parse_url( $uploads_info['baseurl'], PHP_URL_PATH ) );
		}

		// Get all images of the content.
		preg_match_all( '#<img([^>]+?)src=([\'"\\\]*)([^\'"\s\\\>]+)([\'"\\\]*)([^>]*)>#i', $html, $images_match );

		foreach ( $images_match[3] as $k => $image_url ) {

			$parse_url = get_rocket_parse_url( $image_url );
			$path      = trim( $parse_url['path'] );
			$host      = $parse_url['host'];

			if ( empty( $path ) || ! preg_match( '#(' . $wp_content_dirname . $custom_media_uploads_dirname . '|wp-includes)#', $path ) ) {
				continue;
			}

			if ( isset( $cnames[ $host ] ) ) {
				continue;
			}

			// Image path is relative, apply the host to it.
			if ( empty( $host ) ) {
				$image_url = home_url( '/' ) . ltrim( $image_url, '/' );
				$host      = rocket_extract_url_component( $image_url, PHP_URL_HOST );
			}

			// Check if the link isn't external.
			if ( rocket_extract_url_component( home_url(), PHP_URL_HOST ) !== $host ) {
				continue;
			}

			// Check if the URL isn't a DATA-URI.
			if ( false !== strpos( $image_url, 'data:image' ) ) {
				continue;
			}

			$html = str_replace(
				$images_match[0][ $k ],
				/**
				 * Filter the image HTML output with the CDN link
				 *
				 * @since 2.5.5
				 *
				 * @param array $html Output that will be printed.
				 */
				apply_filters(
					'rocket_cdn_images_html',
					sprintf(
						'<img %1$s %2$s %3$s>',
						trim( $images_match[1][ $k ] ),
						'src=' . $images_match[2][ $k ] . get_rocket_cdn_url( $image_url, $zone ) . $images_match[4][ $k ],
						trim( $images_match[5][ $k ] )
					)
				),
				$html
			);
		}
	}

	return $html;
}

/**
 * Replace URL by CDN of all inline styles containing url()
 *
 * @since 2.9
 * @deprecated 3.4
 * @author Remy Perona
 *
 * @param  string $html HTML content of the page.
 * @return string modified HTML content
 */
function rocket_cdn_inline_styles( $html ) {
	_deprecated_function( __FUNCTION__ . '()', '3.4' );
	if ( is_preview() || empty( $html ) || defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) {
		return $html;
	}

	$zone = array(
		'all',
		'images',
	);

	$cnames = get_rocket_cdn_cnames( $zone );
	if ( $cnames ) {
		preg_match_all( '/url\((?![\'\"]?data)[\"\']?([^\)\"\']+)[\"\']?\)/i', $html, $matches );

		if ( (bool) $matches ) {
			foreach ( $matches[1] as $k => $url ) {
				$url = str_replace( array( ' ', '\t', '\n', '\r', '\0', '\x0B', '"', "'", '&quot;', '&#039;' ), '', $url );

				if ( '#' === substr( $url, 0, 1 ) ) {
					continue;
				}

				$url      = get_rocket_cdn_url( $url, $zone );
				$property = str_replace( $matches[1][ $k ], $url, $matches[0][ $k ] );
				$html     = str_replace( $matches[0][ $k ], $property, $html );
			}
		}
	}

	return $html;
}

/**
 * Replace URL by CDN for custom files
 *
 * @since 2.9
 * @deprecated 3.4
 * @author Remy Perona
 *
 * @param string $html HTML content of the page.
 * @return string modified HTML content
 */
function rocket_cdn_custom_files( $html ) {
	_deprecated_function( __FUNCTION__ . '()', '3.4' );
	if ( is_preview() || empty( $html ) || defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) {
		return $html;
	}

	$image_types = [
		'jpg',
		'jpeg',
		'jpe',
		'png',
		'gif',
		'webp',
		'bmp',
		'tiff',
	];

	$other_types = [
		'mp3',
		'ogg',
		'mp4',
		'm4v',
		'avi',
		'mov',
		'flv',
		'swf',
		'webm',
		'pdf',
		'doc',
		'docx',
		'txt',
		'zip',
		'tar',
		'bz2',
		'tgz',
		'rar',
	];

	$zones = array_filter( array_unique( get_rocket_option( 'cdn_zone', [] ) ) );

	if ( empty( $zones ) ) {
		return $html;
	}

	if ( ! in_array( 'all', $zones, true ) && ! in_array( 'images', $zones, true ) ) {
		return $html;
	}

	$cdn_zones  = [];
	$file_types = [];

	if ( in_array( 'images', $zones, true ) ) {
		$cdn_zones[] = 'images';
		$file_types  = array_merge( $file_types, $image_types );
	}

	if ( in_array( 'all', $zones, true ) ) {
		$cdn_zones[] = 'all';
		$file_types  = array_merge( $file_types, $image_types, $other_types );
	}

	$cnames = get_rocket_cdn_cnames( $cdn_zones );

	if ( empty( $cnames ) ) {
		return $html;
	}

	/**
	 * Filters the filetypes allowed for the CDN
	 *
	 * @since 2.9
	 * @author Remy Perona
	 *
	 * @param array $filetypes Array of file types.
	 */
	$file_types = apply_filters( 'rocket_cdn_custom_filetypes', $file_types );
	$file_types = implode( '|', $file_types );

	preg_match_all( '#<a[^>]+?href=[\'"]?([^"\'>]+\.(?:' . $file_types . '))[\'"]?[^>]*>#i', $html, $matches );

	if ( ! (bool) $matches ) {
		return $html;
	}

	foreach ( $matches[1] as $key => $url ) {
		$url  = trim( $url, " \t\n\r\0\x0B\"'" );
		$url  = get_rocket_cdn_url( $url, $cdn_zones );
		$src  = str_replace( $matches[1][ $key ], $url, $matches[0][ $key ] );
		$html = str_replace( $matches[0][ $key ], $src, $html );
	}

	return $html;
}

/**
 * Replace URL by CDN of all scripts and styles enqueues with WordPress functions
 *
 * @since 2.9 Only add protocol if $src is an absolute url
 * @since 2.1
 * @deprecated 3.4
 *
 * @param  string $src URL of the file.
 * @return string modified URL
 */
function rocket_cdn_enqueue( $src ) {
	_deprecated_function( __FUNCTION__ . '()', '3.4' );
	// Don't use CDN if in admin, in login page, in register page or in a post preview.
	if ( is_admin() || is_preview() || in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ), true ) || defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) {
		return $src;
	}

	if ( rocket_extract_url_component( $src, PHP_URL_HOST ) !== '' ) {
		$src = rocket_add_url_protocol( $src );
	}

	$zone = array( 'all', 'css_and_js' );

	// Add only CSS zone.
	if ( 'style_loader_src' === current_filter() ) {
		$zone[] = 'css';
	}

	// Add only JS zone.
	if ( 'script_loader_src' === current_filter() ) {
		$zone[] = 'js';
	}

	$cnames = get_rocket_cdn_cnames( $zone );
	if ( $cnames ) {
		// Check if the path isn't empty.
		if ( trim( rocket_extract_url_component( $src, PHP_URL_PATH ), '/' ) !== '' ) {
			$src = get_rocket_cdn_url( $src, $zone );
		}
	}

	return $src;
}

/**
 * Get all files we don't allow to get in CDN.
 *
 * @since 2.5
 * @deprecated 3.4
 *
 * @return string A pipe-separated list of rejected files.
 */
function get_rocket_cdn_reject_files() {
	_deprecated_function( __FUNCTION__ . '()', '3.4', '\WP_Rocket\Subscriber\CDN\CDN::get_excluded_files()' );

	$files = get_rocket_option( 'cdn_reject_files', [] );

	/**
	 * Filter the rejected files.
	 *
	 * @since 2.5
	 *
	 * @param array $files List of rejected files.
	*/
	$files = (array) apply_filters( 'rocket_cdn_reject_files', $files );
	$files = array_filter( $files );
	$files = array_flip( array_flip( $files ) );

	return implode( '|', $files );
}

/**
 * Conflict with Envira Gallery: changes the URL argument if using WP Rocket CDN and Envira
 *
 * @since 2.6.5
 * @since 3.4
 *
 * @param array $args An array of arguments.
 * @return array Updated array of arguments
 */
function rocket_cdn_resize_image_args_on_envira_gallery( $args ) {
	_deprecated_function( __FUNCTION__ . '()', '3.4' );
	if ( ! isset( $args['url'] ) || (int) get_rocket_option( 'cdn' ) === 0 ) {
		return $args;
	}

	$cnames_host = array_flip( get_rocket_cnames_host() );
	$url_host    = rocket_extract_url_component( $args['url'], PHP_URL_HOST );
	$home_host   = rocket_extract_url_component( home_url(), PHP_URL_HOST );

	if ( isset( $cnames_host[ $url_host ] ) ) {
		$args['url'] = str_replace( $url_host, $home_host , $args['url'] );
	}

	return $args;
}

/**
 * Conflict with Envira Gallery: changes the resized URL if using WP Rocket CDN and Envira
 *
 * @since 2.6.5
 * @since 3.4
 *
 * @param string $url Resized image URL.
 * @return string Resized image URL using the CDN URL
 */
function rocket_cdn_resized_url_on_envira_gallery( $url ) {
	_deprecated_function( __FUNCTION__ . '()', '3.4' );
	if ( (int) get_rocket_option( 'cdn' ) === 0 ) {
		return $url;
	}

	$url = get_rocket_cdn_url( $url, array( 'all', 'images' ) );
	return $url;
}

/**
 * Apply CDN settings to Beaver Builder parallax.
 *
 * @since  3.2.1
 * @deprecated 3.4
 * @author Grégory Viguier
 *
 * @param  array $attrs HTML attributes.
 * @return array
 */
function rocket_beaver_builder_add_cdn_to_parallax( $attrs ) {
	_deprecated_function( __FUNCTION__ . '()', '3.4' );

	if ( ! empty( $attrs['data-parallax-image'] ) ) {
		$attrs['data-parallax-image'] = get_rocket_cdn_url( $attrs['data-parallax-image'], [ 'all', 'images' ] );
	}

	return $attrs;
}

if ( class_exists( 'WR2X_Admin' ) ) :
	/**
	 * Conflict with WP Retina x2: Apply CDN on srcset attribute.
	 *
	 * @since 2.9.1 Use global $wr2x_admin
	 * @since 2.5.5
	 * @deprecated 3.4
	 *
	 * @param string $url URL of the image.
	 * @return string Updated URL with CDN
	 */
	function rocket_cdn_on_images_from_wp_retina_x2( $url ) {
		_deprecated_function( __FUNCTION__ . '()', '3.4' );

		global $wr2x_admin;

		if ( ! method_exists( $wr2x_admin, 'is_pro' ) || ! $wr2x_admin->is_pro() ) {
			return $url;
		}

		$cdn_domain = get_option( 'wr2x_cdn_domain' );

		if ( ! empty( $cdn_domain ) ) {
			return $url;
		}

		return get_rocket_cdn_url( $url, array( 'all', 'images' ) );
	}
endif;

/**
 * Conflict with Avada theme and WP Rocket CDN
 *
 * @since 2.6.1
 * @deprecated 3.4
 *
 * @param array  $vars An array of variables.
 * @param string $handle Name of the avada resource.
 * @return array updated array of variables
 */
function rocket_fix_cdn_for_avada_theme( $vars, $handle ) {
	_deprecated_function( __FUNCTION__ . '()', '3.4' );
	if ( 'avada-dynamic' === $handle && get_rocket_option( 'cdn' ) ) {
		$src                        = get_rocket_cdn_url( get_template_directory_uri() . '/assets/less/theme/dynamic.less' );
		$vars['template-directory'] = sprintf( '~"%s"', dirname( dirname( dirname( dirname( $src ) ) ) ) );
		$vars['lessurl']            = sprintf( '~"%s"', dirname( $src ) );
	}
	return $vars;
}

/**
 * Conflict with Aqua Resizer & IrishMiss Framework: Apply CDN without blank src!!
 *
 * @since 2.5.8 Add compatibility with IrishMiss Framework
 * @since 2.5.5
 * @deprecated 3.4
 */
function rocket_cdn_on_aqua_resizer() {
	_deprecated_function( __FUNCTION__ . '()', '3.4' );

	if ( function_exists( 'aq_resize' ) || function_exists( 'miss_display_image' ) ) {
		remove_filter( 'wp_get_attachment_url' , 'rocket_cdn_file', PHP_INT_MAX );
		add_filter( 'rocket_lazyload_html', 'rocket_add_cdn_on_custom_attr' );
	}
}

/**
 * Conflict with Revolution Slider & Master Slider: Apply CDN on data-lazyload|data-src attribute.
 *
 * @since 2.5.5
 * @deprecated 3.4
 */
function rocket_cdn_on_sliders_with_lazyload() {
	_deprecated_function( __FUNCTION__ . '()', '3.4' );

	if ( class_exists( 'RevSliderFront' ) || class_exists( 'Master_Slider' ) ) {
		add_filter( 'rocket_cdn_images_html', 'rocket_add_cdn_on_custom_attr' );
	}
}

