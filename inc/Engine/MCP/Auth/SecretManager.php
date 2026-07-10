<?php
/**
 * Site Secret Manager.
 *
 * Generates, stores, and rotates the 256-bit HMAC secret used to sign all
 * MCP JWTs.  Regenerating the secret immediately invalidates every outstanding
 * access and refresh token site-wide.
 */

declare(strict_types=1);

namespace WP_Rocket\Engine\MCP\Auth;

use WP_Rocket\Engine\Activation\ActivationInterface;

/**
 * Secret Manager.
 */
class SecretManager implements ActivationInterface {

	/**
	 * WordPress option key where the JWT signing secret is stored.
	 */
	const OPTION_KEY = 'mcp_jwt_secret';

	/**
	 * Registers this class's activation callback.
	 *
	 * @return void
	 */
	public function activate() {
		add_action( 'rocket_activation', [ self::class, 'ensure_secret' ] );
	}

	/**
	 * Return the current site JWT signing secret, generating one if absent.
	 *
	 * Uses add_option() for the initial write so that two concurrent requests
	 * racing on first activation cannot both store different secrets — only one
	 * caller's add_option() succeeds; the other reads back the winner's value.
	 *
	 * @return string Hex-encoded 256-bit secret.
	 */
	public static function get_secret(): string {
		$secret = (string) get_option( self::OPTION_KEY, '' );
		if ( '' !== $secret ) {
			return $secret;
		}

		$candidate = self::generate();
		if ( ! add_option( self::OPTION_KEY, $candidate, '', false ) ) {
			// Another request won the race; read back whatever was stored.
			$candidate = (string) get_option( self::OPTION_KEY );
		}

		return $candidate;
	}

	/**
	 * Ensure a secret exists; create one on first activation.
	 *
	 * Idempotent — safe to call on every activation.
	 *
	 * @return void
	 */
	public static function ensure_secret(): void {
		self::get_secret();
	}

	/**
	 * Regenerate the site secret, invalidating all current MCP sessions.
	 *
	 * @return void
	 */
	public static function regenerate(): void {
		update_option( self::OPTION_KEY, self::generate(), false );
	}

	/**
	 * Generate a fresh 256-bit random secret as a hex string.
	 *
	 * @return string 64-character hex string.
	 */
	private static function generate(): string {
		return bin2hex( random_bytes( 32 ) );
	}
}
