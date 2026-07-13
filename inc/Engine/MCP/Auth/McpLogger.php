<?php
/**
 * MCP structured logger.
 *
 * Single choke-point for all [MCP] error_log calls so that:
 *  - every log line carries a consistent prefix for grepping
 *  - sensitive values (tokens, passwords) are never written
 *  - callers pass structured context arrays rather than building strings
 *
 * Usage:
 *   McpLogger::log( 'TOKEN', 'grant received', [ 'grant_type' => $gt ] );
 *
 * Grep all MCP logs:
 *   grep '\[MCP\]' /path/to/debug.log
 */

declare(strict_types=1);

namespace WP_Rocket\Engine\MCP\Auth;

/**
 * McpLogger.
 */
class McpLogger {

	/**
	 * Write a structured [MCP] log entry.
	 *
	 * Pass $debug_only = true for verbose happy-path traces (e.g. per-request
	 * header dumps, per-ability registration details) that are useful when
	 * diagnosing issues but too noisy for production.  Security events and
	 * failure paths should always use $debug_only = false (the default).
	 *
	 * Debug-only entries fire when WP_DEBUG is true.
	 *
	 * @param string               $scope      Short uppercase scope tag, e.g. 'TOKEN', 'VALIDATOR'.
	 * @param string               $message    Human-readable description.
	 * @param array<string, mixed> $context    Key-value pairs serialised as JSON.
	 * @param bool                 $debug_only When true, only log if WP_DEBUG is enabled.
	 * @return void
	 */
	public static function log( string $scope, string $message, array $context = [], bool $debug_only = false ): void {
		if ( $debug_only && ! self::is_debug_enabled() ) {
			return;
		}

		$line = sprintf(
			'[MCP][%s] %s %s',
			strtoupper( $scope ),
			$message,
			empty( $context ) ? '' : wp_json_encode( $context )
		);
		error_log( trim( $line ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}

	/**
	 * Whether MCP debug logging is enabled.
	 *
	 * True when WP_DEBUG is true.
	 *
	 * @return bool
	 */
	private static function is_debug_enabled(): bool {
		return defined( 'WP_DEBUG' ) && WP_DEBUG;
	}

	/**
	 * Capture and sanitize all HTTP request headers for logging.
	 *
	 * - Authorization values are truncated: first 12 chars + '...' + last 6 chars.
	 * - Cookie header is replaced with '<redacted>'.
	 * - All other values are preserved as-is.
	 *
	 * @return array<string, string> Sanitized header map (lowercase names).
	 */
	public static function safe_request_headers(): array {
		$headers = [];

		if ( function_exists( 'getallheaders' ) ) {
			// getallheaders() always returns an array on PHP 7.3+ once it exists.
			foreach ( getallheaders() as $name => $value ) {
				$key             = strtolower( (string) $name );
				$headers[ $key ] = self::sanitize_header( $key, (string) $value );
			}
		}

		return $headers;
	}

	/**
	 * Capture the raw request body up to a safe size limit.
	 *
	 * JSON bodies are decoded and re-encoded for normalisation; form bodies
	 * are returned as-is.  Authorization/password fields inside the body are
	 * redacted.
	 *
	 * @param int $max_bytes Maximum bytes to read from php://input (default 8 KB).
	 * @return string Sanitised body string suitable for logging.
	 */
	public static function safe_request_body( int $max_bytes = 8192 ): string {
		$raw = (string) file_get_contents( 'php://input' );

		if ( '' === $raw ) {
			return '(empty)';
		}

		$raw = substr( $raw, 0, $max_bytes );

		$decoded = json_decode( $raw, true );
		if ( is_array( $decoded ) ) {
			$decoded = self::redact_sensitive_fields( $decoded );
			return (string) wp_json_encode( $decoded );
		}

		// Form-encoded: redact known sensitive keys.
		parse_str( $raw, $form );
		$form = self::redact_sensitive_fields( $form );
		return (string) http_build_query( $form );
	}

	/**
	 * Sanitize a single header value.
	 *
	 * @param string $name  Lowercase header name.
	 * @param string $value Header value.
	 * @return string Safe value for log output.
	 */
	private static function sanitize_header( string $name, string $value ): string {
		if ( 'cookie' === $name ) {
			return '<redacted>';
		}

		if ( 'authorization' === $name ) {
			// Preserve scheme prefix (e.g. "Bearer ") and truncate the credential.
			$space = strpos( $value, ' ' );
			if ( false !== $space ) {
				$scheme     = substr( $value, 0, $space + 1 );
				$credential = substr( $value, $space + 1 );
				return $scheme . self::truncate_credential( $credential );
			}
			return self::truncate_credential( $value );
		}

		return $value;
	}

	/**
	 * Truncate a credential string for safe logging.
	 *
	 * Keeps first 12 chars + '...' + last 6 chars.
	 *
	 * @param string $credential Raw credential value.
	 * @return string Truncated representation.
	 */
	private static function truncate_credential( string $credential ): string {
		$len = strlen( $credential );
		if ( $len <= 20 ) {
			return str_repeat( '*', $len );
		}
		return substr( $credential, 0, 12 ) . '...' . substr( $credential, -6 );
	}

	/**
	 * Recursively redact known sensitive field names from an array.
	 *
	 * @param array<string, mixed> $data Input data.
	 * @return array<string, mixed> Redacted copy.
	 */
	private static function redact_sensitive_fields( array $data ): array {
		$sensitive = [ 'code', 'code_verifier', 'client_secret', 'access_token', 'refresh_token', 'password' ];

		foreach ( $data as $key => $value ) {
			if ( in_array( strtolower( (string) $key ), $sensitive, true ) ) {
				$data[ $key ] = '<redacted>';
			} elseif ( is_array( $value ) ) {
				$data[ $key ] = self::redact_sensitive_fields( $value );
			}
		}

		return $data;
	}
}
