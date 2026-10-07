<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Tracking;

class ChannelDetector {

	const CHANNEL_UI       = 'UI';
	const CHANNEL_MCP      = 'MCP';
	const CHANNEL_REST_API = 'REST API';
	const CHANNEL_CLI      = 'CLI';
	const CHANNEL_CRON     = 'CRON';
	const CHANNEL_UNKNOWN  = 'Unknown';

	/**
	 * Detect interaction channel
	 *
	 * @return string
	 */
	public function detect(): string {
		return $this->resolve_channel();
	}

	/**
	 * Resolves the interaction channel from the current request's signals.
	 *
	 * @return string
	 */
	private function resolve_channel(): string {
		if ( rocket_get_constant( 'WP_CLI', false ) ) {
			return $this->detect_cli_channel();
		}

		if ( rocket_get_constant( 'REST_REQUEST', false ) ) {
			return $this->detect_rest_channel();
		}

		if ( rocket_get_constant( 'DOING_CRON', false ) ) {
			return self::CHANNEL_CRON;
		}

		if ( rocket_get_constant( 'WP_ADMIN', false ) ) {
			return self::CHANNEL_UI;
		}

		return self::CHANNEL_UNKNOWN;
	}

	/**
	 * Detect the rest channel.
	 *
	 * @return string
	 */
	private function detect_rest_channel(): string {
		$rest_route = ltrim( (string) get_query_var( 'rest_route', '' ), '/' );

		if ( '' === $rest_route && isset( $GLOBALS['wp'] ) ) {
			$rest_route = ltrim( (string) ( $GLOBALS['wp']->query_vars['rest_route'] ?? '' ), '/' );
		}

		if ( '' === $rest_route ) {
			$rest_route = ltrim( sanitize_text_field( wp_unslash( (string) ( $_GET['rest_route'] ?? '' ) ) ), '/' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		$mcp_prefixes = [ 'mcp/', 'wp-abilities/' ];
		foreach ( $mcp_prefixes as $prefix ) {
			if ( 0 === strpos( $rest_route, $prefix ) ) {
				return self::CHANNEL_MCP;
			}
		}

		if ( $this->has_valid_dashboard_nonce() ) {
			return self::CHANNEL_UI;
		}

		return self::CHANNEL_REST_API;
	}

	/**
	 * Determines if the request is from WPR dashboard
	 *
	 * @return bool
	 */
	private function has_valid_dashboard_nonce(): bool {
		$nonce = isset( $_SERVER['HTTP_X_WP_NONCE'] ) ? sanitize_text_field( wp_unslash( (string) $_SERVER['HTTP_X_WP_NONCE'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( '' === $nonce ) {
			return false;
		}

		return (bool) wp_verify_nonce( $nonce, 'wp_rest' );
	}

	/**
	 * Distinguish MCP from a direct WP-CLI command.
	 *
	 * @return string
	 */
	private function detect_cli_channel(): string {
		$argv = isset( $_SERVER['argv'] ) ? (array) $_SERVER['argv'] : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash

		if ( in_array( 'mcp', $argv, true ) && in_array( 'serve', $argv, true ) ) {
			return self::CHANNEL_MCP;
		}

		return self::CHANNEL_CLI;
	}
}
