<?php

defined( 'ABSPATH' ) || exit;

/**
 * Check if request is from Cloudflare
 *
 * @since  3.4.1
 * @author Soponar Cristina
 *
 * @return bool
 */
function rocket_is_cloudflare() {
	if ( ! isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
		return false;
	}
	// Check if original ip has already been restored, e.g. by nginx - assume it was from cloudflare then.
	if ( isset( $_SERVER['REMOTE_ADDR'] ) && $_SERVER['REMOTE_ADDR'] === $_SERVER['HTTP_CF_CONNECTING_IP'] ) {
		return true;
	}

	return rocket_is_cf_ip();
}

/**
 * Check if a request comes from a CloudFlare IP.
 *
 * @since  3.4.1
 * @author Soponar Cristina
 *
 * @return bool
 */
function rocket_is_cf_ip() {
	// Store original remote address in $original_ip.
	$original_ip = filter_input( INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP );
	if ( ! isset( $original_ip ) ) {
		return false;
	}

	$cf_ips_values = get_transient( 'rocket_cloudflare_ips' );

	// Cloudflare IPS should always be populated because the code runs before loading Cloudflare addon.
	if ( false === $cf_ips_values ) {
		$cf_ips_values = (object) [
			'success' => true,
			'result'  => (object) [],
		];

		$cf_ips_values->result->ipv4_cidrs = [
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

		$cf_ips_values->result->ipv6_cidrs = [
			'2400:cb00::/32',
			'2405:8100::/32',
			'2405:b500::/32',
			'2606:4700::/32',
			'2803:f800::/32',
			'2c0f:f248::/32',
			'2a06:98c0::/29',
		];
	}

	if ( strpos( $original_ip, ':' ) === false ) {
		$cf_ip_ranges = $cf_ips_values->result->ipv4_cidrs;
		foreach ( $cf_ip_ranges as $range ) {
			if ( rocket_ipv4_in_range( $original_ip, $range ) ) {
				return true;
			}
		}
	} else {
		$cf_ip_ranges = $cf_ips_values->result->ipv6_cidrs;
		$ipv6         = get_rocket_ipv6_full( $original_ip );
		foreach ( $cf_ip_ranges as $range ) {
			if ( rocket_ipv6_in_range( $ipv6, $range ) ) {
				return true;
			}
		}
	}

	return false;
}

/**
 * Fixes Cloudflare Flexible SSL redirect loop
 *
 * @since  3.4.1
 * @author Soponar Cristina
 */
function rocket_fix_cf_flexible_ssl() {
	$is_cf = rocket_is_cloudflare();
	if ( $is_cf ) {
		// Fixes Flexible SSL.
		if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO'] ) {
			$_SERVER['HTTPS'] = 'on';
		}
	}
}
