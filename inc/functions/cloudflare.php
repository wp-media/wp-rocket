<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Validate Cloudflare input data
 *
 * @since 3.4.1
 * @author Soponar Cristina
 *
 * @param string $cf_email         - Cloudflare email.
 * @param string $cf_api_key       - Cloudflare API key.
 * @param string $cf_zone_id       - Cloudflare zone ID.
 * @param bool   $basic_validation - Bypass Cloudflare API user and zone validation.
 * @return Object                  - true if credentials are ok, WP_Error otherwise.
 */
function rocket_is_api_keys_valid_cloudflare( $cf_email, $cf_api_key, $cf_zone_id, $basic_validation = true ) {
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
 * @author Remy Perona
 *
 * @return Object Cloudflare\Api instance if crendentials are set, WP_Error otherwise
 */
function get_rocket_cloudflare_api_instance() {
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
 *
 * @return Object Cloudflare instance & zone_id if credentials are correct, WP_Error otherwise
 */
function get_rocket_cloudflare_instance() {
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
 * Returns the main instance of Cloudflare API to prevent the need to use globals.
 */
$GLOBALS['rocket_cloudflare'] = get_rocket_cloudflare_instance();

/**
 * Test the connection with Cloudflare
 *
 * @since 2.9
 * @author Remy Perona
 *
 * @throws Exception If the connection to Cloudflare failed.
 * @return Object True if connection is successful, WP_Error otherwise
 */
function rocket_cloudflare_valid_auth() {
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
 *
 * @return mixed bool|Array Array of Cloudflare settings, false if any error connection to Cloudflare
 */
function get_rocket_cloudflare_settings() {
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
 *
 * @param string $mode Value for Cloudflare development mode.
 * @throws Exception If any error occurs when doing the API request.
 * @return mixed Object|String Mode value if the update is successful, WP_Error otherwise
 */
function set_rocket_cloudflare_devmode( $mode ) {
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
 *
 * @param string $mode Value for Cloudflare caching level.
 * @throws Exception If any error occurs when doing the API request.
 * @return mixed Object|String Mode value if the update is successful, WP_Error otherwise
 */
function set_rocket_cloudflare_cache_level( $mode ) {
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
 *
 * @param string $mode Value for Cloudflare minification.
 * @throws Exception If any error occurs when doing the API request.
 * @return mixed Object|String Mode value if the update is successful, WP_Error otherwise
 */
function set_rocket_cloudflare_minify( $mode ) {
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
 *
 * @param string $mode Value for Cloudflare Rocket Loader.
 * @throws Exception If any error occurs when doing the API request.
 * @return mixed Object|String Mode value if the update is successful, WP_Error otherwise
 */
function set_rocket_cloudflare_rocket_loader( $mode ) {
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
 *
 * @param string $mode Value for Cloudflare browser cache TTL.
 * @throws Exception If any error occurs when doing the API request.
 * @return mixed Object|String Mode value if the update is successful, WP_Error otherwise
 */
function set_rocket_cloudflare_browser_cache_ttl( $mode ) {
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
 *
 * @throws Exception If any error occurs when doing the API request.
 * @return mixed Object|bool true if the purge is successful, WP_Error otherwise
 */
function rocket_purge_cloudflare() {
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
 *
 * @author Remy Perona
 *
 * @throws Exception If any error occurs when doing the API request.
 * @return Object Result of API request if successful, WP_Error otherwise
 */
function rocket_get_cloudflare_ips() {
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
 * Automatically set Cloudflare development mode value to off after 3 hours to reflect Cloudflare behaviour
 *
 * @since 2.9
 * @author Remy Perona
 */
function do_rocket_deactivate_cloudflare_devmode() {
	$options                       = get_option( WP_ROCKET_SLUG );
	$options['cloudflare_devmode'] = 'off';
	update_option( WP_ROCKET_SLUG, $options );
}
add_action( 'rocket_cron_deactivate_cloudflare_devmode', 'do_rocket_deactivate_cloudflare_devmode' );


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
 * @throws Exception          If any error occurs when doing the API request.
 * @return mixed Object|bool  true if the purge is successful, WP_Error otherwise
 */
function rocket_purge_cloudflare_by_url( $post, $purge_urls, $lang ) {
	if ( is_wp_error( $GLOBALS['rocket_cloudflare'] ) ) {
		return $GLOBALS['rocket_cloudflare'];
	}

	try {
		$cf_cache = new Cloudflare\Zone\Cache( $GLOBALS['rocket_cloudflare']->auth );
		$cf_purge = $cf_cache->purge_files( $GLOBALS['rocket_cloudflare']->zone_id, $purge_urls );

		if ( empty( $cf_purge->success ) ) {
			foreach ( $cf_purge->errors as $error ) {
				$errors[] = $error->message;
			}

			$errors = implode( ', ', $errors );
			throw new Exception( $errors );
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
 * Checks if CF has the $action_value set as a Page Rule.
 *
 * @since  3.4.2
 * @author Soponar Cristina
 *
 * @param  String $action_value - cache_everything.
 * @return mixed  Object|bool   - true / false if $action_value was found or not, WP_Error otherwise
 *
 * @throws Exception          If any error occurs when doing the API request.
 */
function rocket_cf_has_page_rule( $action_value ) {
	if ( is_wp_error( $GLOBALS['rocket_cloudflare'] ) ) {
		return $GLOBALS['rocket_cloudflare'];
	}

	try {
		$cf_page_rules = new Cloudflare\Zone\Pagerules( $GLOBALS['rocket_cloudflare']->auth );
		$cf_page_rule  = $cf_page_rules->list_pagerules( $GLOBALS['rocket_cloudflare']->zone_id, 'active' );

		if ( empty( $cf_page_rule->success ) ) {
			foreach ( $cf_page_rule->errors as $error ) {
				$errors[] = $error->message;
			}

			$errors = implode( ', ', $errors );
			throw new Exception( $errors );
		}

		$cf_page_rule_arr = (array) $cf_page_rule;
		return in_array( $action_value, $cf_page_rule_arr );

	} catch ( Exception $e ) {
		return new WP_Error( 'cloudflare_page_rule_failed', $e->getMessage() );
	}
}


/**
 * Purge Cloudflare cache automatically if Cache Everything is set as a Page Rule
 *
 * @since 3.4.2
 * @author Soponar Cristina
 */
function rocket_auto_purge_cloudflare() {
	if ( ! get_rocket_option( 'do_cloudflare' ) ) {
		return;
	}

	if ( ! current_user_can( 'rocket_purge_cloudflare_cache' ) ) {
		return;
	}

	$cf_cache_everything = rocket_cf_has_page_rule( 'cache_everything' );
	if ( is_wp_error( $cf_cache_everything ) ) {
		return;
	}
	if ( ! $cf_cache_everything ) {
		return;
	}

	// Purge CloudFlare.
	$cf_purge = rocket_purge_cloudflare();

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
add_action( 'after_rocket_clean_domain', 'rocket_auto_purge_cloudflare' );


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
function rocket_auto_purge_cloudflare_by_url( $post, $purge_urls, $lang ) {
	if ( ! get_rocket_option( 'do_cloudflare' ) ) {
		return;
	}

	if ( ! current_user_can( 'rocket_purge_cloudflare_cache' ) ) {
		return;
	}

	$cf_cache_everything = rocket_cf_has_page_rule( 'cache_everything' );
	if ( is_wp_error( $cf_cache_everything ) ) {
		return;
	}
	if ( ! $cf_cache_everything ) {
		return;
	}

	// Purge CloudFlare.
	$cf_purge = rocket_purge_cloudflare_by_url( $post, $purge_urls, $lang );

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
add_action( 'after_rocket_clean_post', 'rocket_auto_purge_cloudflare_by_url', 10, 3 );
