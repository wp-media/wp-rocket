<?php

defined( 'ABSPATH' ) || exit;

/**
 * Set Real IP from CloudFlare
 *
 * @since 2.8.16 Uses CloudFlare API v4 to get CloudFlare IPs
 * @since 2.5.4
 * @source cloudflare.php - https://wordpress.org/plugins/cloudflare/
 */
function rocket_set_real_ip_cloudflare() {
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

		$remote_addr      = filter_input( INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP );
		$cf_connecting_ip = filter_input( INPUT_SERVER, 'HTTP_CF_CONNECTING_IP', FILTER_VALIDATE_IP );

		if ( strpos( $remote_addr, ':' ) === false ) {
			$cf_ip_ranges = $cf_ips_values->result->ipv4_cidrs;

			// IPV4: Update the REMOTE_ADDR value if the current REMOTE_ADDR value is in the specified range.
			foreach ( $cf_ip_ranges as $range ) {
				if ( rocket_ipv4_in_range( $remote_addr, $range ) ) {
					if ( $cf_connecting_ip ) {
						$_SERVER['REMOTE_ADDR'] = $cf_connecting_ip;
					}
					break;
				}
			}
		}
		else {
			$cf_ip_ranges = $cf_ips_values->result->ipv6_cidrs;

			$ipv6 = get_rocket_ipv6_full( $remote_addr );
			foreach ( $cf_ip_ranges as $range ) {
				if ( rocket_ipv6_in_range( $ipv6, $range ) ) {
					if ( $cf_connecting_ip ) {
						$_SERVER['REMOTE_ADDR'] = $cf_connecting_ip;
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
add_action( 'init', 'rocket_set_real_ip_cloudflare', 1 );
