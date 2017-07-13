<?php
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

/**
 * Get a CloudFlare\Api instance
 *
 * @since 2.8.21
 * @author Remy Perona
 *
 * @return Object CloudFlare\Api instance if crendentials are set, WP_Error otherwise
 */
function get_rocket_cloudflare_api_instance() {
	$cf_email   = get_rocket_option( 'cloudflare_email', null );
	$cf_api_key = ( defined( 'WP_ROCKET_CF_API_KEY' ) ) ? WP_ROCKET_CF_API_KEY : get_rocket_option( 'cloudflare_api_key', null );

	if ( ! isset( $cf_email, $cf_api_key ) ) {
		return new WP_Error( 'cloudflare_credentials_empty', __( 'Cloudflare Email and API key are not set', 'rocket' ) );
	}

	return new Cloudflare\Api( $cf_email, $cf_api_key );
}

/**
 * Get a CloudFlare\Api instance & the zone_id corresponding to the domain
 *
 * @since 2.8.21 Get the zone ID from the options
 * @since 2.8.18 Add try/catch to prevent fatal error Uncaugh Exception
 * @since 2.8.16 Update to CloudFlare API v4
 * @since 2.5
 *
 * @return Object CloudFlare instance & zone_id if credentials are correct, WP_Error otherwise
 */
function get_rocket_cloudflare_instance() {
	$cf_api_instance = get_rocket_cloudflare_api_instance();
	if ( is_wp_error( $cf_api_instance )  ) {
		return $cf_api_instance;
	}

	$cf_instance = (object) [ 'auth' => $cf_api_instance ];
	$cf_zone_id  = get_rocket_option( 'cloudflare_zone_id', null );

	if ( ! isset( $cf_zone_id ) ) {
		return new WP_Error( 'cloudflare_no_zone_id', __( 'No Zone ID set in the WP Rocket settings', 'rocket' ) );
	}

	$cf_instance = (object) [ 'auth' => $cf_api_instance, 'zone_id' => $cf_zone_id ];

	return $cf_instance;
}

/**
 * Returns the main instance of CloudFlare API to prevent the need to use globals.
 */
$GLOBALS['rocket_cloudflare'] = get_rocket_cloudflare_instance();

/**
 * Test the connection with CloudFlare
 *
 * @since 2.9
 * @author Remy Perona
 *
 * @throws Exception If the connection to CloudFlare failed.
 * @return Object True if connection is successful, WP_Error otherwise
 */
function rocket_cloudflare_valid_auth() {
	$cf_api_instance = get_rocket_cloudflare_api_instance();
	if ( is_wp_error( $cf_api_instance ) ) {
		return $cf_api_instance;
	}

	try {
		$cf_zone_instance = new CloudFlare\Zone( $cf_api_instance );
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
 * Get Zones linked to a CloudFlare account
 *
 * @since 2.9
 * @author Remy Perona
 *
 * @return Array List of zones or default no domain
 */
function get_rocket_cloudflare_zones() {
	$cf_api_instance = get_rocket_cloudflare_api_instance();
	$domains = array(
		'' => __( 'Choose a domain from the list', 'rocket' ),
	);

	if ( is_wp_error( $cf_api_instance ) ) {
		return $domains;
	}

	try {
		$cf_zone_instance        = new CloudFlare\Zone( $cf_api_instance );
		$cf_zones                = $cf_zone_instance->zones( null, 'active', null, 50 );
		$cf_zones_list           = $cf_zones->result;

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
 * Get all the current CloudFlare settings for a given domain.
 *
 * @since 2.8.16 Update to CloudFlare API v4
 * @since 2.5
 *
 * @return mixed bool|Array Array of CloudFlare settings, false if any error connection to CloudFlare
 */
function get_rocket_cloudflare_settings() {
	if ( is_wp_error( $GLOBALS['rocket_cloudflare'] ) ) {
		return $GLOBALS['rocket_cloudflare'];
	}

	try {
		$cf_settings_instance = new CloudFlare\Zone\Settings( $GLOBALS['rocket_cloudflare']->auth );
	    $cf_settings          = $cf_settings_instance->settings( $GLOBALS['rocket_cloudflare']->zone_id );
	    $cf_minify            = $cf_settings->result[16]->value;
	    $cf_minify_value      = 'on';

	    if ( 'off' === $cf_minify->js || 'off' === $cf_minify->css || 'off' === $cf_minify->html ) {
			$cf_minify_value = 'off';
	    }

	    $cf_settings_array  = array(
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
 * Set the CloudFlare Development mode.
 *
 * @since 2.9 Now returns a value
 * @since 2.8.16 Update to CloudFlare API v4
 * @since 2.5
 *
 * @param string $mode Value for CloudFlare development mode.
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
		$cf_settings = new CloudFlare\Zone\Settings( $GLOBALS['rocket_cloudflare']->auth );
		$cf_return = $cf_settings->change_development_mode( $GLOBALS['rocket_cloudflare']->zone_id, $value );

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
 * Set the CloudFlare Caching level.
 *
 * @since 2.9 Now returns a value
 * @since 2.8.16 Update to CloudFlare API v4
 * @since 2.5
 *
 * @param string $mode Value for CloudFlare caching level.
 * @throws Exception If any error occurs when doing the API request.
 * @return mixed Object|String Mode value if the update is successful, WP_Error otherwise
 */
function set_rocket_cloudflare_cache_level( $mode ) {
	if ( is_wp_error( $GLOBALS['rocket_cloudflare'] ) ) {
		return $GLOBALS['rocket_cloudflare'];
	}

	try {
		$cf_settings = new CloudFlare\Zone\Settings( $GLOBALS['rocket_cloudflare']->auth );
		$cf_return = $cf_settings->change_cache_level( $GLOBALS['rocket_cloudflare']->zone_id, $mode );

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
 * Set the CloudFlare Minification.
 *
 * @since 2.9 Now returns a value
 * @since 2.8.16 Update to CloudFlare API v4
 * @since 2.5
 *
 * @param string $mode Value for CloudFlare minification.
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
		$cf_settings = new CloudFlare\Zone\Settings( $GLOBALS['rocket_cloudflare']->auth );
		$cf_return = $cf_settings->change_minify( $GLOBALS['rocket_cloudflare']->zone_id, $cf_minify_settings );

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
 * Set the CloudFlare Rocket Loader.
 *
 * @since 2.9 Now returns value
 * @since 2.8.16 Update to CloudFlare API v4
 * @since 2.5
 *
 * @param string $mode Value for CloudFlare Rocket Loader.
 * @throws Exception If any error occurs when doing the API request.
 * @return mixed Object|String Mode value if the update is successful, WP_Error otherwise
 */
function set_rocket_cloudflare_rocket_loader( $mode ) {
	if ( is_wp_error( $GLOBALS['rocket_cloudflare'] ) ) {
		return $GLOBALS['rocket_cloudflare'];
	}

	try {
		$cf_settings = new CloudFlare\Zone\Settings( $GLOBALS['rocket_cloudflare']->auth );
		$cf_return = $cf_settings->change_rocket_loader( $GLOBALS['rocket_cloudflare']->zone_id, $mode );

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
 * Set the Browser Cache TTL in CloudFlare.
 *
 * @since 2.9 Now returns value
 * @since 2.8.16
 *
 * @param string $mode Value for CloudFlare browser cache TTL.
 * @throws Exception If any error occurs when doing the API request.
 * @return mixed Object|String Mode value if the update is successful, WP_Error otherwise
 */
function set_rocket_cloudflare_browser_cache_ttl( $mode ) {
	if ( is_wp_error( $GLOBALS['rocket_cloudflare'] ) ) {
		return $GLOBALS['rocket_cloudflare'];
	}

	try {
		$cf_settings = new CloudFlare\Zone\Settings( $GLOBALS['rocket_cloudflare']->auth );
		$cf_return = $cf_settings->change_browser_cache_ttl( $GLOBALS['rocket_cloudflare']->zone_id, (int) $mode );

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
 * Purge CloudFlare cache.
 *
 * @since 2.9 Now returns value
 * @since 2.8.16 Update to CloudFlare API v4
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
		$cf_cache = new CloudFlare\Zone\Cache( $GLOBALS['rocket_cloudflare']->auth );
		$cf_purge = $cf_cache->purge( $GLOBALS['rocket_cloudflare']->zone_id, true );

		if ( ! isset( $cf_purge->success ) || empty( $cf_purge->success ) ) {
			foreach ( $cf_purge->errors as $error ) {
				$errors[] = $error->message;
			}

			$errors = implode( ', ', $errors );
			throw new Exception( $errors );
		}

		return true;

	} catch ( Exception $e ) {
		return new WP_Error( 'cloudflare_purge_failed', $e->getMessage() );
	}
}

/**
 * Get CloudFlare IPs.
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

	if ( false === ( $cf_ips = get_transient( 'rocket_cloudflare_ips' ) ) ) {
		try {
			$cf_ips_instance = new CloudFlare\IPs( $cf_instance );
			$cf_ips = $cf_ips_instance->ips();

			if ( isset( $cf_ips->success ) && $cf_ips->success ) {
				set_transient( 'rocket_cloudflare_ips', $cf_ips, 2 * WEEK_IN_SECONDS );
			} else {
				throw new Exception( 'Error connecting to CloudFlare' );
			}
		} catch ( Exception $e ) {
			$cf_ips = (object) [ 'success' => true, 'result' => (object) [] ];
			$cf_ips->result->ipv4_cidrs = array(
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
				'199.27.128.0/21',
			);

			$cf_ips->result->ipv6_cidrs = array(
				'2400:cb00::/32',
				'2405:8100::/32',
				'2405:b500::/32',
				'2606:4700::/32',
				'2803:f800::/32',
				'2c0f:f248::/32',
				'2a06:98c0::/29',
			);

			set_transient( 'rocket_cloudflare_ips', $cf_ips, 2 * WEEK_IN_SECONDS );
			return $cf_ips;
		}
	}

	return $cf_ips;
}

/**
 * Automatically set CloudFlare development mode value to off after 3 hours to reflect CloudFlare behaviour
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
