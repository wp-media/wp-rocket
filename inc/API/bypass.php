<?php

defined( 'ABSPATH' ) || exit;

/**
 * Indicate to bypass rocket optimizations.
 *
 * Checks for "nowprocket" query string in the url to bypass rocket processes.
 *
 * @since 3.7
 *
 * @return bool True to indicate should bypass; false otherwise.
 */
function rocket_bypass() {
	static $bypass = null;

	if ( rocket_get_constant( 'WP_ROCKET_IS_TESTING', false ) ) {
		$bypass = null;
	}

	if ( ! is_null( $bypass ) ) {
		return $bypass;
	}

	$bypass = isset( $_GET['nowprocket'] ) && 0 !== $_GET['nowprocket']; // phpcs:ignore WordPress.Security.NonceVerification

	return $bypass;
}
