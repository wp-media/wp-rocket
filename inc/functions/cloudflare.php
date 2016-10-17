<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

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
    	return new WP_Error( 'cloudflare_credentials_empty', __( 'CloudFlare Email & API Key are not set', 'rocket' ) );
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
 * @return mixed bool|object CloudFlare instance & zone_id if credentials are correct, false otherwise
 */
function get_rocket_cloudflare_instance() {
	$cf_api_instance = get_rocket_cloudflare_api_instance();
	if ( is_wp_error( $cf_api_instance )  ) {
    	return false;
    }

    $cf_zone_id = get_rocket_option( 'cloudflare_zone_id', null );

    if ( ! isset( $cf_zone_id ) ) {
        return false;
    }
    
    $cf_instance = ( object ) [ 'auth' => $cf_api_instance, 'zone_id' => $cf_zone_id ];

    return $cf_instance;
}

/**
 * Returns the main instance of CloudFlare API to prevent the need to use globals.
 */
$GLOBALS['rocket_cloudflare'] = get_rocket_cloudflare_instance();

/**
 * Get all the current CloudFlare settings for a given domain.
 *
 * @since 2.8.16 Update to CloudFlare API v4
 * @since 2.5
 *
 * @return Array
 */
function get_rocket_cloudflare_settings() {
	if ( ! is_object( $GLOBALS['rocket_cloudflare'] ) ) {
		return false;
	}
	
	try {
		$cf_settings_instance = new CloudFlare\Zone\Settings( $GLOBALS['rocket_cloudflare']->auth );
		$cf_settings          = $cf_settings_instance->settings( $GLOBALS['rocket_cloudflare']->zone_id );
		$cf_minify            = $cf_settings->result[16]->value;
		$cf_minify_value      = 'on';
	
		if ( $cf_minify->js === 'off' || $cf_minify->css === 'off' || $cf_minify->html === 'off' ) {
	    	$cf_minify_value = 'off';
		}
	
		$cf_settings_array  = array(
	    	'cache_level'       => $cf_settings->result[5]->value,
	    	'minify'            => $cf_minify_value,
	    	'rocket_loader'     => $cf_settings->result[25]->value,
	    	'browser_cache_ttl' => $cf_settings->result[3]->value
		);
	
		return $cf_settings_array;
	} catch ( Exception $e ) {
		return false;
	}
}

/**
 * Set the CloudFlare Development mode.
 *
 * @since 2.8.16 Update to CloudFlare API v4
 * @since 2.5
 *
 * @return void
 */
function set_rocket_cloudflare_devmode( $mode ) {
	if ( ! is_object( $GLOBALS['rocket_cloudflare'] ) ) {
		return false;
	}

    if ( ( int ) $mode === 0 ) {
        $value = 'off';
    } else if ( ( int ) $mode === 1 ) {
        $value = 'on';
    }

	try {
		$cf_settings = new CloudFlare\Zone\Settings( $GLOBALS['rocket_cloudflare']->auth );
		$cf_settings->change_development_mode( $GLOBALS['rocket_cloudflare']->zone_id, $value );
	} catch ( Exception $e ) {}
}

/**
 * Set the CloudFlare Caching level.
 *
 * @since 2.8.16 Update to CloudFlare API v4
 * @since 2.5
 *
 * @return void
 */
function set_rocket_cloudflare_cache_level( $mode ) {
	if ( ! is_object( $GLOBALS['rocket_cloudflare'] ) ) {
		return false;
	}
	
	try {
		$cf_settings = new CloudFlare\Zone\Settings( $GLOBALS['rocket_cloudflare']->auth );
		$cf_settings->change_cache_level( $GLOBALS['rocket_cloudflare']->zone_id, $mode );
	} catch ( Exception $e ) {}
}

/**
 * Set the CloudFlare Minification.
 *
 * @since 2.8.16 Update to CloudFlare API v4
 * @since 2.5
 *
 * @return void
 */
function set_rocket_cloudflare_minify( $mode ) {
	if ( ! is_object( $GLOBALS['rocket_cloudflare'] ) ) {
		return false;
	}

    $cf_minify_settings = array(
        'css'  => $mode,
        'html' => $mode,
        'js'   => $mode
    );
	
	try {
		$cf_settings = new CloudFlare\Zone\Settings( $GLOBALS['rocket_cloudflare']->auth );
		$cf_settings->change_minify( $GLOBALS['rocket_cloudflare']->zone_id, $cf_minify_settings );
	} catch ( Exception $e ) {}
}

/**
 * Set the CloudFlare Rocket Loader.
 *
 * @since 2.8.16 Update to CloudFlare API v4
 * @since 2.5
 *
 * @return void
 */
function set_rocket_cloudflare_rocket_loader( $mode ) {
	if ( ! is_object( $GLOBALS['rocket_cloudflare'] ) ) {
		return false;
	}
	
	try {
		$cf_settings = new CloudFlare\Zone\Settings( $GLOBALS['rocket_cloudflare']->auth );
		$cf_settings->change_rocket_loader( $GLOBALS['rocket_cloudflare']->zone_id, $mode );
	} catch ( Exception $e ) {}
}

/**
 * Set the Browser Cache TTL in CloudFlare.
 *
 * @since 2.8.16
 *
 * @return void
 */
function set_rocket_cloudflare_browser_cache_ttl( $mode ) {
	if ( ! is_object( $GLOBALS['rocket_cloudflare'] ) ) {
		return false;
	}
	
	try {
		$cf_settings = new CloudFlare\Zone\Settings( $GLOBALS['rocket_cloudflare']->auth );
		$cf_settings->change_browser_cache_ttl( $GLOBALS['rocket_cloudflare']->zone_id, $mode );
	} catch ( Exception $e ) {}
}

/**
 * Purge CloudFlare cache.
 *
 * @since 2.8.16 Update to CloudFlare API v4
 * @since 2.5
 *
 * @return void
 */
function rocket_purge_cloudflare() {
	if ( ! is_object( $GLOBALS['rocket_cloudflare'] ) ) {
		return false;
	}
	
	try {
		$cf_cache = new CloudFlare\Zone\Cache( $GLOBALS['rocket_cloudflare']->auth );
		$cf_cache->purge( $GLOBALS['rocket_cloudflare']->zone_id, true );
	} catch ( Exception $e ) {}
}

/**
 * Get CloudFlare IPs.
 *
 * @since 2.8.21 Save IPs in a transient to prevent calling the API everytime
 * @since 2.8.16
 *
 * @author Remy Perona
 *
 * @return mixed Bool|Object Result of API request, false otherwise
 */
function rocket_get_cloudflare_ips() {
    $cf_instance = get_rocket_cloudflare_api_instance();

    if ( is_wp_error( $cf_instance ) ) {
		return false;
	}

    if ( false === ( $cf_ips = get_transient( 'rocket_cloudflare_ips' ) ) ) {
        try {
            $cf_ips_instance = new CloudFlare\IPs( $cf_instance );
            $cf_ips = $cf_ips_instance->ips();

            if ( isset( $cf_ips->success ) && $cf_ips->success ) {
                set_transient(  'rocket_cloudflare_ips', $cf_ips, 2 * WEEK_IN_SECONDS );
            } else {
                throw new Exception( 'Error connecting to CloudFlare' );
            }
        } catch ( Exception $e ) {
            $cf_ips = ( object ) [ 'success' => true, 'result' => ( object ) [] ];
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

            return $cf_ips;
        }
    }

    return $cf_ips;
}