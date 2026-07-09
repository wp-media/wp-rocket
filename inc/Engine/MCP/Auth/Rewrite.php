<?php
/**
 * OAuth 2.1 Rewrite Rules.
 *
 * Registers the WordPress rewrite rules and query var used to route the
 * five OAuth endpoints (authorize, authorize-callback, token, consent,
 * revoke).
 */

declare( strict_types=1 );

namespace WP_Rocket\Engine\MCP\Auth;

use WP_Rocket\Engine\Activation\ActivationInterface;

/**
 * Registers OAuth endpoint rewrite rules and the query var used to route them.
 */
class Rewrite implements ActivationInterface {

	/**
	 * WordPress query var used to route OAuth endpoint requests.
	 */
	const OAUTH_QUERY_VAR = 'mcp_oauth_endpoint'; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound

	/**
	 * Registers this class's activation callback.
	 *
	 * @return void
	 */
	public function activate() {
		add_action( 'rocket_activation', [ $this, 'register_oauth_rewrite_rules' ] );
	}

	/**
	 * Register WordPress rewrite rules for all five OAuth endpoints.
	 *
	 * Called both on the 'init' action (normal page load) and during plugin
	 * activation to ensure rules are present before flush_rewrite_rules().
	 *
	 * @return void
	 */
	public function register_oauth_rewrite_rules(): void {
		add_rewrite_rule( '^oauth/authorize$', 'index.php?' . self::OAUTH_QUERY_VAR . '=authorize', 'top' );
		add_rewrite_rule( '^oauth/authorize-callback$', 'index.php?' . self::OAUTH_QUERY_VAR . '=authorize-callback', 'top' );
		add_rewrite_rule( '^oauth/token$', 'index.php?' . self::OAUTH_QUERY_VAR . '=token', 'top' );
		add_rewrite_rule( '^oauth/consent$', 'index.php?' . self::OAUTH_QUERY_VAR . '=consent', 'top' );
		add_rewrite_rule( '^oauth/revoke$', 'index.php?' . self::OAUTH_QUERY_VAR . '=revoke', 'top' );
	}

	/**
	 * Add the OAuth query var to WordPress's list of recognised vars.
	 *
	 * @param string[] $vars Existing query vars.
	 * @return string[] Modified list.
	 */
	public function add_oauth_query_vars( array $vars ): array {
		$vars[] = self::OAUTH_QUERY_VAR;

		return $vars;
	}
}
