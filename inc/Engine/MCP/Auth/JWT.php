<?php
/**
 * JWT Helper — pure HS256 implementation with no external dependencies.
 */

declare(strict_types=1);

namespace WP_Rocket\Engine\MCP\Auth;

/**
 * JWT encoder / decoder.
 *
 * Implements HS256 (HMAC-SHA256) signing. All methods are static so callers
 * never need to instantiate the class.
 */
class JWT {

	/**
	 * Encode a payload as a signed JWT.
	 *
	 * @param array<string, mixed> $payload Claims to include in the token.
	 * @param string               $secret  HMAC signing secret.
	 * @return string Signed JWT string.
	 */
	public static function encode( array $payload, string $secret ): string {
		$header = self::base64url_encode(
			(string) wp_json_encode(
				[
					'typ' => 'JWT',
					'alg' => 'HS256',
				]
			)
		);
		$body   = self::base64url_encode( (string) wp_json_encode( $payload ) );
		$sig    = self::base64url_encode( hash_hmac( 'sha256', $header . '.' . $body, $secret, true ) );

		return $header . '.' . $body . '.' . $sig;
	}

	/**
	 * Decode and verify a JWT.
	 *
	 * Returns null if the signature is invalid or (when $verify_expiry is true)
	 * the token has expired.  Pass false for $verify_expiry when the caller
	 * needs to act on an expired token — e.g. the revocation endpoint, which
	 * must accept expired tokens per RFC 7009.
	 *
	 * @param string $token         JWT string.
	 * @param string $secret        HMAC signing secret.
	 * @param bool   $verify_expiry Whether to reject tokens whose exp has passed.
	 * @return array<string, mixed>|null Decoded payload, or null on failure.
	 */
	public static function decode( string $token, string $secret, bool $verify_expiry = true ): ?array {
		$parts = explode( '.', $token );
		if ( 3 !== count( $parts ) ) {
			return null;
		}

		list( $header, $body, $signature ) = $parts;

		// Pin the algorithm. We only ever issue and verify HS256; rejecting any
		// other alg (notably 'none') defends against algorithm-substitution
		// attacks explicitly rather than relying on the HMAC comparison alone.
		$header_data = json_decode( self::base64url_decode( $header ), true );
		if ( ! is_array( $header_data ) || 'HS256' !== ( $header_data['alg'] ?? '' ) ) {
			return null;
		}

		$expected_sig = self::base64url_encode( hash_hmac( 'sha256', $header . '.' . $body, $secret, true ) );
		if ( ! hash_equals( $expected_sig, $signature ) ) {
			return null;
		}

		$payload = json_decode( self::base64url_decode( $body ), true );
		if ( ! is_array( $payload ) ) {
			return null;
		}

		if ( $verify_expiry && isset( $payload['exp'] ) && (int) $payload['exp'] < time() ) {
			return null;
		}

		return $payload;
	}

	/**
	 * Base64URL-encode a binary string.
	 *
	 * @param string $data Raw bytes to encode.
	 * @return string URL-safe base64 without padding.
	 */
	public static function base64url_encode( string $data ): string {
		return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}

	/**
	 * Base64URL-decode a string.
	 *
	 * @param string $data URL-safe base64 string.
	 * @return string Decoded binary string.
	 */
	private static function base64url_decode( string $data ): string {
		return (string) base64_decode( strtr( $data, '-_', '+/' ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
	}
}
