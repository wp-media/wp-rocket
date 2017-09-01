<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Set Real IP from CloudFlare
 *
 * @since 2.8.16 Uses CloudFlare API v4 to get CloudFlare IPs
 * @since 2.5.4
 * @source cloudflare.php - https://wordpress.org/plugins/cloudflare/
 */
function rocket_set_real_ip_cloudflare() {
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

	// Let people know that the CF WP plugin is turned on, except if white label is active.
	if ( ! headers_sent() ) {
		if ( rocket_is_white_label() ) {
			$powered_by = get_rocket_option( 'wl_plugin_name' );
		} else {
			$powered_by = 'WP Rocket ' . WP_ROCKET_VERSION;
		}

		header( 'X-CF-Powered-By: ' . $powered_by );
	}
}
add_action( 'init', 'rocket_set_real_ip_cloudflare' , 1 );
