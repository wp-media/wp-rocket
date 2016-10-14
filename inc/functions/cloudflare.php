<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

/**
 * Get a CloudFlare\Api instance
 *
 * @since 2.9
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
    
    $cf_instance = ( object ) [ 'auth' => $cf_api_instance ];

    try {
        $zone_instance = new CloudFlare\Zone( $cf_instance->auth );
	    $cf_domain     = get_rocket_option( 'cloudflare_domain', null );
	    $zone          = $zone_instance->zones( $cf_domain );
        
        if ( ! isset( $zone->result[0]->id ) ) {
            throw new Exception( __( 'The domain name is invalid', 'rocket' ) );
        }

        $cf_instance->zone_id = $zone->result[0]->id;         
        return $cf_instance;
    } catch ( Exception $e ) {
        return new WP_Error( 'cloudflare_credentials_invalid', $e->getMessage() );
    }
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
            throw new Exception( __( 'Connection to CloudFlare failed', 'rocket' ) );
        }

        if ( $cf_zones->success === true ) {
            return true;
        }
    } catch( Exception $e ) {
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
        '' => __( 'Choose a domain from the list', 'rocket' )
    );
    
	if ( is_wp_error( $cf_api_instance ) ) {
    	return $domains;
    }
    
    try {
    	$cf_zone_instance        = new CloudFlare\Zone( $cf_api_instance );
        $cf_zones                = $cf_zone_instance->zones();
        $cf_zones_list           = $cf_zones->result;
        
        
        if ( ! ( bool ) $cf_zones_list ) {
            $domains[] = __( 'No domain available in your CloudFlare account', 'rocket' );
        
            return $domains;
        }
        
        foreach( $cf_zones_list as $cf_zone ) {
            $domains[ $cf_zone->name ] = $cf_zone->name;
        }
        
        return $domains;
    } catch( Exception $e ) {
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
    } catch( Exception $e ) {
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
 * @return mixed Object|String Mode value if the update is successful, WP_Error otherwise
 */
function set_rocket_cloudflare_devmode( $mode ) {
	if ( is_wp_error( $GLOBALS['rocket_cloudflare'] ) ) {
		return $GLOBALS['rocket_cloudflare'];
	}

    if ( ( int ) $mode === 0 ) {
        $value = 'off';
    } else if ( ( int ) $mode === 1 ) {
        $value = 'on';
    }

    try {
        $cf_settings = new CloudFlare\Zone\Settings( $GLOBALS['rocket_cloudflare']->auth );
        $cf_return = $cf_settings->change_development_mode( $GLOBALS['rocket_cloudflare']->zone_id, $value );

        if ( ! isset( $cf_return->success ) || empty( $cf_return->success ) ) {
            foreach( $cf_return->errors as $error ) {
                $errors[] = $error->message;
            }

            $errors = implode( ', ', $errors );
            throw new Exception( $errors );
        }

        return $value;
    } catch( Exception $e ) {
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
            foreach( $cf_return->errors as $error ) {
                $errors[] = $error->message;
            }

            $errors = implode( ', ', $errors );
            throw new Exception( $errors );
        }

        return $mode;
    } catch( Exception $e ) {
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
 * @return mixed Object|String Mode value if the update is successful, WP_Error otherwise
 */
function set_rocket_cloudflare_minify( $mode ) {
	if ( is_wp_error( $GLOBALS['rocket_cloudflare'] ) ) {
		return $GLOBALS['rocket_cloudflare'];
	}

    $cf_minify_settings = array(
        'css'  => $mode,
        'html' => $mode,
        'js'   => $mode
    );

    try {
        $cf_settings = new CloudFlare\Zone\Settings( $GLOBALS['rocket_cloudflare']->auth );
        $cf_return = $cf_settings->change_minify( $GLOBALS['rocket_cloudflare']->zone_id, $cf_minify_settings );

        if ( ! isset( $cf_return->success ) || empty( $cf_return->success ) ) {
            foreach( $cf_return->errors as $error ) {
                $errors[] = $error->message;
            }

            $errors = implode( ', ', $errors );
            throw new Exception( $errors );
        }

        return $mode;
    } catch( Exception $e ) {
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
            foreach( $cf_return->errors as $error ) {
                $errors[] = $error->message;
            }

            $errors = implode( ', ', $errors );
            throw new Exception( $errors );
        }

        return $mode;
    } catch( Exception $e ) {
        return new WP_Error( 'cloudflare_rocket_loader', $e->getMessage() );
    }
}

/**
 * Set the Browser Cache TTL in CloudFlare.
 *
 * @since 2.9 Now returns value
 * @since 2.8.16
 *
 * @return mixed Object|String Mode value if the update is successful, WP_Error otherwise
 */
function set_rocket_cloudflare_browser_cache_ttl( $mode ) {
	if ( is_wp_error( $GLOBALS['rocket_cloudflare'] ) ) {
		return $GLOBALS['rocket_cloudflare'];
	}

    try {
        $cf_settings = new CloudFlare\Zone\Settings( $GLOBALS['rocket_cloudflare']->auth );
        $cf_return = $cf_settings->change_browser_cache_ttl( $GLOBALS['rocket_cloudflare']->zone_id, $mode );

        if ( ! isset( $cf_return->success ) || empty( $cf_return->success ) ) {
            foreach( $cf_return->errors as $error ) {
                $errors[] = $error->message;
            }

            $errors = implode( ', ', $errors );
            throw new Exception( $errors );
        }

        return $mode;
    } catch( Exception $e ) {
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
            foreach( $cf_purge->errors as $error ) {
                $errors[] = $error->message;
            }

            $errors = implode( ', ', $errors );
            throw new Exception( $errors );
        }

        return true;

    } catch( Exception $e ) {
        return new WP_Error( 'cloudflare_purge_failed', $e->getMessage() );
    }
	
}

/**
 * Get CloudFlare IPs.
 *
 * @since 2.8.16
 *
 * @return Object Result of API request if successful, WP_Error otherwise
 */
function rocket_get_cloudflare_ips() {
    $cf_instance = get_rocket_cloudflare_api_instance();
    if ( is_wp_error( $cf_instance ) ) {
		return $cf_instance;
	}

    try {
       $cf_ips_instance = new CloudFlare\IPs( $cf_instance );
       return $cf_ips_instance->ips();
    } catch( Exception $e ) {
        return new WP_Error( 'Error', $e->getMessage() );
    }
}