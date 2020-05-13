<?php
// phpcs:ignoreFile

defined( 'ABSPATH' ) || exit;

/**
 * Class aliases.
 */
class_alias( '\WP_Rocket\Engine\Admin\Settings\Page', '\WP_Rocket\Admin\Settings\Page' );
class_alias( '\WP_Rocket\Engine\Admin\Settings\Render', '\WP_Rocket\Admin\Settings\Render' );
class_alias( '\WP_Rocket\Engine\Admin\Settings\Settings', '\WP_Rocket\Admin\Settings\Settings' );
class_alias( '\WP_Rocket\Engine\Admin\Settings\ServiceProvider', '\WP_Rocket\ServiceProvider\Settings' );
class_alias( '\WP_Rocket\Engine\Admin\Settings\Subscriber', '\WP_Rocket\Subscriber\Admin\Settings\Page_Subscriber' );
class_alias( '\WP_Rocket\Engine\Preload\AbstractPreload', '\WP_Rocket\Preload\Abstract_Preload' );
class_alias( '\WP_Rocket\Engine\Preload\AbstractProcess', '\WP_Rocket\Preload\Process' );
class_alias( '\WP_Rocket\Engine\Preload\FullProcess', '\WP_Rocket\Preload\Full_Process' );
class_alias( '\WP_Rocket\Engine\Preload\Homepage', '\WP_Rocket\Preload\Homepage' );
class_alias( '\WP_Rocket\Engine\Preload\PartialPreloadSubscriber', '\WP_Rocket\Subscriber\Preload\Partial_Preload_Subscriber' );
class_alias( '\WP_Rocket\Engine\Preload\PartialProcess', '\WP_Rocket\Preload\Partial_Process' );
class_alias( '\WP_Rocket\Engine\Preload\PreloadSubscriber', '\WP_Rocket\Subscriber\Preload\Preload_Subscriber' );
class_alias( '\WP_Rocket\Engine\Preload\ServiceProvider', '\WP_Rocket\ServiceProvider\Preload_Subscribers' );
class_alias( '\WP_Rocket\Engine\Preload\Sitemap', '\WP_Rocket\Preload\Sitemap' );
class_alias( '\WP_Rocket\Engine\Preload\SitemapPreloadSubscriber', '\WP_Rocket\Subscriber\Preload\Sitemap_Preload_Subscriber' );
class_alias( '\WP_Rocket\Engine\Optimization\GoogleFonts\Combine', '\WP_Rocket\Optimization\CSS\Combine_Google_Fonts' );
class_alias( '\WP_Rocket\Engine\Optimization\GoogleFonts\Subscriber', '\WP_Rocket\Subscriber\Optimization\Combine_Google_Fonts_Subscriber' );

//RocketCDN Start
class_alias('\WP_Rocket\Engine\CDN\RocketCDN\AdminPageSubscriber', '\WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber');
class_alias('\WP_Rocket\Engine\CDN\RocketCDN\APIClient', '\WP_Rocket\CDN\RocketCDN\APIClient');
class_alias('\WP_Rocket\Engine\CDN\RocketCDN\CDNOptionsManager', '\WP_Rocket\CDN\RocketCDN\CDNOptionsManager');
class_alias('\WP_Rocket\Engine\CDN\RocketCDN\DataManagerSubscriber', '\WP_Rocket\Subscriber\CDN\RocketCDN\DataManagerSubscriber');
class_alias('\WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber', '\WP_Rocket\Subscriber\CDN\RocketCDN\NoticesSubscriber');
class_alias('\WP_Rocket\Engine\CDN\RocketCDN\RESTSubscriber', '\WP_Rocket\Subscriber\CDN\RocketCDN\RESTSubscriber');
class_alias('\WP_Rocket\Engine\CDN\RocketCDN\ServiceProvider', '\WP_Rocket\ServiceProvider\RocketCDN');
//RocketCDN End

/**
 * Removes Minification, DNS Prefetch, LazyLoad, Defer JS when on an AMP version of a post with the AMP for WordPress plugin from Auttomatic
 *
 * @since 2.8.10 Compatibility with wp_resource_hints in WP 4.6
 * @since 2.7
 * @deprecated 3.5.2
 *
 * @author Remy Perona
 */
function rocket_disable_options_on_amp() {
	_deprecated_function( __FUNCTION__ . '()', '3.5.2', '\WP_Rocket\ThirdParty\Plugins\Optimization\AMP::disable_options_on_amp()' );
	global $wp_filter;

	if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
		remove_filter( 'wp_resource_hints', 'rocket_dns_prefetch', 10, 2 );
		add_filter( 'do_rocket_lazyload', '__return_false' );
		unset( $wp_filter['rocket_buffer'] );

		// this filter is documented in inc/front/protocol.php.
		$do_rocket_protocol_rewrite = apply_filters( 'do_rocket_protocol_rewrite', false ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

		if ( ( get_rocket_option( 'do_cloudflare', 0 ) && get_rocket_option( 'cloudflare_protocol_rewrite', 0 ) || $do_rocket_protocol_rewrite ) ) {
			remove_filter( 'rocket_buffer', 'rocket_protocol_rewrite', PHP_INT_MAX );
			remove_filter( 'wp_calculate_image_srcset', 'rocket_protocol_rewrite_srcset', PHP_INT_MAX );
		}
	}
}

/**
 * Validate Cloudflare input data
 *
 * @since 3.4.1
 * @deprecated 3.5
 * @author Soponar Cristina
 *
 * @param string $cf_email         - Cloudflare email.
 * @param string $cf_api_key       - Cloudflare API key.
 * @param string $cf_zone_id       - Cloudflare zone ID.
 * @param bool   $basic_validation - Bypass Cloudflare API user and zone validation.
 * @return Object                  - true if credentials are ok, WP_Error otherwise.
 */
function rocket_is_api_keys_valid_cloudflare( $cf_email, $cf_api_key, $cf_zone_id, $basic_validation = true ) {
	_deprecated_function( __FUNCTION__ . '()', '3.5', '\WP_Rocket\Subscriber\Tools\Cloudflare_Subscriber::is_api_keys_valid()' );
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
 * @deprecated 3.5
 * @author Soponar Cristina
 *
 * @return Object Cloudflare\Api instance if crendentials are set, WP_Error otherwise
 */
function get_rocket_cloudflare_api_instance() {
	_deprecated_function( __FUNCTION__ . '()', '3.5' );
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
 * @deprecated 3.5
 *
 * @return Object Cloudflare instance & zone_id if credentials are correct, WP_Error otherwise
 */
function get_rocket_cloudflare_instance() {
	_deprecated_function( __FUNCTION__ . '()', '3.5', '\WP_Rocket\Subscriber\Tools\Cloudflare_Subscriber::get_instance()' );
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
 * @deprecated 3.5
 * @author Remy Perona
 *
 * @throws Exception If the connection to Cloudflare failed.
 * @return Object True if connection is successful, WP_Error otherwise
 */
function rocket_cloudflare_valid_auth() {
	_deprecated_function( __FUNCTION__ . '()', '3.5' );
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
 * @deprecated 3.5
 *
 * @return mixed bool|Array Array of Cloudflare settings, false if any error connection to Cloudflare
 */
function get_rocket_cloudflare_settings() {
	_deprecated_function( __FUNCTION__ . '()', '3.5', '\WP_Rocket\Subscriber\Tools\Cloudflare_Subscriber::get_settings()' );
}


/**
 * Set the Cloudflare Development mode.
 *
 * @since 2.9 Now returns a value
 * @since 2.8.16 Update to Cloudflare API v4
 * @since 2.5
 * @deprecated 3.5
 *
 * @param string $mode Value for Cloudflare development mode.
 * @throws Exception If any error occurs when doing the API request.
 * @return mixed Object|String Mode value if the update is successful, WP_Error otherwise
 */
function set_rocket_cloudflare_devmode( $mode ) {
	_deprecated_function( __FUNCTION__ . '()', '3.5', '\WP_Rocket\Subscriber\Tools\Cloudflare_Subscriber::set_devmode()' );
}


/**
 * Set the Cloudflare Caching level.
 *
 * @since 2.9 Now returns a value
 * @since 2.8.16 Update to Cloudflare API v4
 * @since 2.5
 * @deprecated 3.5
 *
 * @param string $mode Value for Cloudflare caching level.
 * @throws Exception If any error occurs when doing the API request.
 * @return mixed Object|String Mode value if the update is successful, WP_Error otherwise
 */
function set_rocket_cloudflare_cache_level( $mode ) {
	_deprecated_function( __FUNCTION__ . '()', '3.5', '\WP_Rocket\Subscriber\Tools\Cloudflare_Subscriber::set_cache_level()' );
}

/**
 * Set the Cloudflare Minification.
 *
 * @since 2.9 Now returns a value
 * @since 2.8.16 Update to Cloudflare API v4
 * @since 2.5
 * @deprecated 3.5
 *
 * @param string $mode Value for Cloudflare minification.
 * @throws Exception If any error occurs when doing the API request.
 * @return mixed Object|String Mode value if the update is successful, WP_Error otherwise
 */
function set_rocket_cloudflare_minify( $mode ) {
	_deprecated_function( __FUNCTION__ . '()', '3.5', '\WP_Rocket\Subscriber\Tools\Cloudflare_Subscriber::set_minify()' );
}


/**
 * Set the Cloudflare Rocket Loader.
 *
 * @since 2.9 Now returns value
 * @since 2.8.16 Update to Cloudflare API v4
 * @since 2.5
 * @deprecated 3.5
 *
 * @param string $mode Value for Cloudflare Rocket Loader.
 * @throws Exception If any error occurs when doing the API request.
 * @return mixed Object|String Mode value if the update is successful, WP_Error otherwise
 */
function set_rocket_cloudflare_rocket_loader( $mode ) {
	_deprecated_function( __FUNCTION__ . '()', '3.5', '\WP_Rocket\Subscriber\Tools\Cloudflare_Subscriber::set_rocket_loader()' );
}


/**
 * Set the Browser Cache TTL in Cloudflare.
 *
 * @since 2.9 Now returns value
 * @since 2.8.16
 * @deprecated 3.5
 *
 * @param string $mode Value for Cloudflare browser cache TTL.
 * @throws Exception If any error occurs when doing the API request.
 * @return mixed Object|String Mode value if the update is successful, WP_Error otherwise
 */
function set_rocket_cloudflare_browser_cache_ttl( $mode ) {
	_deprecated_function( __FUNCTION__ . '()', '3.5', '\WP_Rocket\Subscriber\Tools\Cloudflare_Subscriber::set_browser_cache_ttl()' );
}


/**
 * Purge Cloudflare cache.
 *
 * @since 2.9 Now returns value
 * @since 2.8.16 Update to Cloudflare API v4
 * @since 2.5
 * @deprecated 3.5
 *
 * @throws Exception If any error occurs when doing the API request.
 * @return mixed Object|bool true if the purge is successful, WP_Error otherwise
 */
function rocket_purge_cloudflare() {
	_deprecated_function( __FUNCTION__ . '()', '3.5', '\WP_Rocket\Subscriber\Tools\Cloudflare_Subscriber::purge_cloudflare()' );
}

/**
 * Get Cloudflare IPs.
 *
 * @since 2.8.21 Save IPs in a transient to prevent calling the API everytime
 * @since 2.8.16
 * @deprecated 3.5
 *
 * @author Remy Perona
 *
 * @throws Exception If any error occurs when doing the API request.
 * @return Object Result of API request if successful, WP_Error otherwise
 */
function rocket_get_cloudflare_ips() {
	_deprecated_function( __FUNCTION__ . '()', '3.5', '\WP_Rocket\Subscriber\Tools\Cloudflare_Subscriber::get_cloudflare_ips()' );
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
 * @deprecated 3.5
 *
 * @source cloudflare.php - https://wordpress.org/plugins/cloudflare/
 */
function rocket_set_real_ip_cloudflare() {
	_deprecated_function( __FUNCTION__ . '()', '3.5', '\WP_Rocket\Subscriber\Tools\Cloudflare_Subscriber::set_real_ip()' );
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
 * @deprecated 3.5
 *
 */
function rocket_cloudflare_purge_result() {
	_deprecated_function( __FUNCTION__ . '()', '3.5', '\WP_Rocket\Subscriber\Tools\Cloudflare_Subscriber::maybe_print_notice()' );
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
 * @deprecated 3.5
 *
 */
function do_admin_post_rocket_purge_cloudflare() {
	_deprecated_function( __FUNCTION__ . '()', '3.5', '\WP_Rocket\Subscriber\Tools\Cloudflare_Subscriber::do_purge_cloudflare()' );
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
 * @deprecated 3.5
 */
function rocket_cloudflare_update_settings() {
	_deprecated_function( __FUNCTION__ . '()', '3.5', '\WP_Rocket\Subscriber\Tools\Cloudflare_Subscriber::maybe_print_update_settings_notice()' );
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
 * Purge all the domain
 *
 * @since 2.6.8
 * @deprecated 3.5
 *
 * @param string $root The path of home cache file.
 * @param string $lang The current lang to purge.
 * @param string $url  The home url.
 */
function rocket_varnish_clean_domain( $root, $lang, $url ) {
	_deprecated_function( __FUNCTION__ . '()', '3.5', 'WP_Rocket\Subscriber\Addons\Varnish\VarnishSubscriber::clean_domain()' );
	rocket_varnish_http_purge( trailingslashit( $url ) . '?vregex' );
}

/**
 * Purge a specific page
 *
 * @since 2.6.8
 * @deprecated 3.5
 *
 * @param string $url The url to purge.
 */
function rocket_varnish_clean_file( $url ) {
	_deprecated_function( __FUNCTION__ . '()', '3.5', 'WP_Rocket\Subscriber\Addons\Varnish\VarnishSubscriber::clean_file()' );
	rocket_varnish_http_purge( trailingslashit( $url ) . '?vregex' );
}

/**
 * Purge the homepage and its pagination
 *
 * @since 2.6.8
 * @deprecated 3.5
 *
 * @param string $root The path of home cache file.
 * @param string $lang The current lang to purge.
 */
function rocket_varnish_clean_home( $root, $lang ) {
	_deprecated_function( __FUNCTION__ . '()', '3.5', 'WP_Rocket\Subscriber\Addons\Varnish\VarnishSubscriber::clean_home()' );
	$home_url            = trailingslashit( get_rocket_i18n_home_url( $lang ) );
	$home_pagination_url = $home_url . trailingslashit( $GLOBALS['wp_rewrite']->pagination_base ) . '?vregex';

	rocket_varnish_http_purge( $home_url );
	rocket_varnish_http_purge( $home_pagination_url );
}

/**
 * Sets the Varnish IP to localhost if Cloudflare is active
 *
 * @since 3.3.5
 * @deprecated 3.5
 * @author Remy Perona
 *
 * @return string
 */
function rocket_varnish_proxy_host() {
	_deprecated_function( __FUNCTION__ . '()', '3.5', 'WP_Rocket\Subscriber\Addons\Cloudflare\CloudflareSubscriber::set_varnish_localhost()' );
	return 'localhost';
}

/**
 * Sets the Host header to the website domain if Cloudflare is active
 *
 * @since 3.3.5
 * @deprecated 3.5
 * @author Remy Perona
 *
 * @return string
 */
function rocket_varnish_proxy_request_host() {
	_deprecated_function( __FUNCTION__ . '()', '3.5', 'WP_Rocket\Subscriber\Addons\Cloudflare\CloudflareSubscriber::set_varnish_purge_request_host()' );
	return wp_parse_url( home_url(), PHP_URL_HOST );
}

/**
 * Send data to Varnish
 *
 * @since 2.6.8
 * @deprecated 3.5
 *
 * @param  string $url The URL to purge.
 * @return void
 */
function rocket_varnish_http_purge( $url ) {
	_deprecated_function( __FUNCTION__ . '()', '3.5', 'WP_Rocket\Addons\Varnish\Varnish::purge()' );
	$parse_url = get_rocket_parse_url( $url );

	$varnish_x_purgemethod = 'default';
	$regex                 = '';

	if ( 'vregex' === $parse_url['query'] ) {
		$varnish_x_purgemethod = 'regex';
		$regex                 = '.*';
	}

	/**
	 * Filter the Varnish IP to call
	 *
	 * @since 2.6.8
	 * @param string The Varnish IP
	 */
	$varnish_ip = apply_filters( 'rocket_varnish_ip', '' );

	if ( defined( 'WP_ROCKET_VARNISH_IP' ) && ! $varnish_ip ) {
		$varnish_ip = WP_ROCKET_VARNISH_IP;
	}

	/**
	 * Filter the HTTP protocol (scheme)
	 *
	 * @since 2.7.3
	 * @param string The HTTP protocol
	 */
	$scheme = apply_filters( 'rocket_varnish_http_purge_scheme', 'http' );

	$parse_url['host'] = ( $varnish_ip ) ? $varnish_ip : $parse_url['host'];
	$purgeme           = $scheme . '://' . $parse_url['host'] . $parse_url['path'] . $regex;

	wp_remote_request(
		$purgeme,
		array(
			'method'      => 'PURGE',
			'blocking'    => false,
			'redirection' => 0,
			/**
			 * Filters the headers to send with the Varnish purge request
			 *
			 * @since 3.1
			 * @author Remy Perona
			 *
			 * @param array $headers Headers to send.
			 */
			'headers'     => apply_filters(
				'rocket_varnish_purge_headers',
				[
					/**
					 * Filters the host value passed in the request headers
					 *
					 * @since 2.8.15
					 * @param string The host
					 */
					'host'           => apply_filters( 'rocket_varnish_purge_request_host', $parse_url['host'] ),
					'X-Purge-Method' => $varnish_x_purgemethod,
				]
			),
		)
	);
}

/**
 * Display a warning notice if WP Rocket scheduled events are not running properly
 *
 * @since 3.5.4 deprecated
 * @since 3.3.7
 * @author Remy Perona
 *
 * @return void
 */
function rocket_warning_cron() {
	_deprecated_function( __FUNCTION__ . '()', '3.5.4', 'WP_Rocket\Engine\Admin\HealthCheck::missed_cron()' );
	$screen = get_current_screen();

	// This filter is documented in inc/admin-bar.php.
	if ( ! current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
		return;
	}

	if ( 'settings_page_wprocket' !== $screen->id ) {
		return;
	}

	$boxes = get_user_meta( get_current_user_id(), 'rocket_boxes', true );

	if ( in_array( __FUNCTION__, (array) $boxes, true ) ) {
		return;
	}

	if ( 0 === (int) get_rocket_option( 'purge_cron_interval' ) && 0 === get_rocket_option( 'async_css' ) && 0 === get_rocket_option( 'manual_preload' ) && 0 === get_rocket_option( 'schedule_automatic_cleanup' ) ) {
		return;
	}

	$events = [
		'rocket_purge_time_event'                      => 'Scheduled Cache Purge',
		'rocket_database_optimization_time_event'      => 'Scheduled Database Optimization',
		'rocket_database_optimization_cron_interval'   => 'Database Optimization Process',
		'rocket_preload_cron_interval'                 => 'Preload',
		'rocket_critical_css_generation_cron_interval' => 'Critical Path CSS Generation Process',
	];

	foreach ( $events as $event => $description ) {
		$timestamp = wp_next_scheduled( $event );

		if ( false === $timestamp ) {
			unset( $events[ $event ] );
			continue;
		}

		if ( $timestamp - time() > 0 ) {
			unset( $events[ $event ] );
			continue;
		}
	}

	if ( empty( $events ) ) {
		return;
	}

	$message = '<p>' . _n( 'The following scheduled event failed to run. This may indicate the CRON system is not running properly, which can prevent some WP Rocket features from working as intended:', 'The following scheduled events failed to run. This may indicate the CRON system is not running properly, which can prevent some WP Rocket features from working as intended:', count( $events ), 'rocket' ) . '</p>';

	$message .= '<ul>';

	foreach ( $events as $description ) {
		$message .= '<li>' . $description . '</li>';
	}

	$message .= '</ul>';
	$message .= '<p>' . __( 'Please contact your host to check if CRON is working.', 'rocket' ) . '</p>';

	rocket_notice_html(
		[
			'status'         => 'warning',
			'dismissible'    => '',
			'message'        => $message,
			'dismiss_button' => __FUNCTION__,
		]
	);
}

/**
 * Add a link "Purge this cache" in the taxonomy edit area
 *
 * @since 3.5.5 deprecated
 * @since 1.0
 *
 * @param array  $actions An array of row action links.
 * @param object $term The term object.
 * @return array Updated array of row action links
 */
function rocket_tag_row_actions( $actions, $term ) {
	_deprecated_function( __FUNCTION__ . '()', '3.5.5', 'WP_Rocket\Engine\Cache\AdminSubscriber::add_purge_term_link()' );
	global $taxnow;

	if ( ! current_user_can( 'rocket_purge_terms' ) ) {
		return $actions;
	}

	$url                     = wp_nonce_url( admin_url( 'admin-post.php?action=purge_cache&type=term-' . $term->term_id . '&taxonomy=' . $taxnow ), 'purge_cache_term-' . $term->term_id );
	$actions['rocket_purge'] = sprintf( '<a href="%s">%s</a>', $url, __( 'Clear this cache', 'rocket' ) );

	return $actions;
}