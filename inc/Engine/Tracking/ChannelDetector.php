<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Tracking;

class ChannelDetector {

	const CHANNEL_UI       = 'UI';
	const CHANNEL_MCP      = 'MCP';
	const CHANNEL_REST_API = 'REST API';
	const CHANNEL_CLI      = 'CLI';


	/**
	 * Detect interaction channel
	 *
	 * @return string
	 */
	public function detect(): string {
		if ( rocket_get_constant( 'WP_CLI', false ) ) {
			return $this->detect_cli_channel();
		}

		if ( rocket_get_constant( 'REST_REQUEST', false ) ) {
			return self::CHANNEL_REST_API;
		}

		if ( rocket_get_constant( 'DOING_AJAX', false ) ) {
			return self::CHANNEL_UI;
		}

		return self::CHANNEL_UI;
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
