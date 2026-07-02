<?php
/**
 * OAuth Request Body Parser.
 *
 * Shared logic for parsing application/json or application/x-www-form-urlencoded
 * request bodies in OAuth endpoints (TokenEndpoint, RevokeEndpoint).
 */

declare(strict_types=1);

namespace WP_Rocket\Engine\MCP\Auth;

/**
 * Provides parse_body() to OAuth endpoint classes.
 */
trait ParseBodyTrait {

	/**
	 * Parse the request body from either JSON or form-encoded content.
	 *
	 * JSON bodies are capped at 32 KB to prevent memory exhaustion from
	 * oversized payloads; form-encoded bodies are read from $_POST which
	 * PHP already limits via the post_max_size ini setting.
	 *
	 * @return array<string, mixed> Associative array of body parameters.
	 */
	private function parse_body(): array {
		$content_type = isset( $_SERVER['CONTENT_TYPE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['CONTENT_TYPE'] ) ) : '';

		if ( false !== strpos( $content_type, 'application/json' ) ) {
			$raw  = substr( (string) file_get_contents( 'php://input' ), 0, 32768 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$body = json_decode( '' !== $raw ? $raw : '{}', true );
			return is_array( $body ) ? $body : [];
		}

		// Form-encoded (application/x-www-form-urlencoded).
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		return array_map( 'sanitize_text_field', wp_unslash( $_POST ) );
	}
}
