<?php
/**
 * MCP Auth Subscriber.
 *
 * Wires the OAuth 2.1 endpoint routing, rewrite rules, and plugin lifecycle
 * hooks via the WP Rocket event manager instead of directly calling
 * add_action / add_filter.
 */

declare( strict_types=1 );

namespace WP_Rocket\Engine\MCP\Auth;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Registers OAuth endpoint routing, activation, and deactivation callbacks.
 */
class Subscriber implements Subscriber_Interface {

	/**
	 * WordPress query var used to route OAuth endpoint requests.
	 */
	const OAUTH_QUERY_VAR = 'mcp_oauth_endpoint'; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound

	/**
	 * Authorize endpoint handler.
	 *
	 * @var AuthorizeEndpoint
	 */
	private AuthorizeEndpoint $authorize_endpoint;

	/**
	 * Authorize callback handler.
	 *
	 * @var AuthorizeCallback
	 */
	private AuthorizeCallback $authorize_callback;

	/**
	 * Token endpoint handler.
	 *
	 * @var TokenEndpoint
	 */
	private TokenEndpoint $token_endpoint;

	/**
	 * Consent endpoint handler.
	 *
	 * @var ConsentEndpoint
	 */
	private ConsentEndpoint $consent_endpoint;

	/**
	 * Revoke endpoint handler.
	 *
	 * @var RevokeEndpoint
	 */
	private RevokeEndpoint $revoke_endpoint;

	/**
	 * Constructor.
	 *
	 * @param AuthorizeEndpoint $authorize_endpoint  Authorization endpoint.
	 * @param AuthorizeCallback $authorize_callback  Authorization callback.
	 * @param TokenEndpoint     $token_endpoint      Token endpoint.
	 * @param ConsentEndpoint   $consent_endpoint    Consent endpoint.
	 * @param RevokeEndpoint    $revoke_endpoint     Revocation endpoint.
	 */
	public function __construct(
		AuthorizeEndpoint $authorize_endpoint,
		AuthorizeCallback $authorize_callback,
		TokenEndpoint $token_endpoint,
		ConsentEndpoint $consent_endpoint,
		RevokeEndpoint $revoke_endpoint
	) {
		$this->authorize_endpoint = $authorize_endpoint;
		$this->authorize_callback = $authorize_callback;
		$this->token_endpoint     = $token_endpoint;
		$this->consent_endpoint   = $consent_endpoint;
		$this->revoke_endpoint    = $revoke_endpoint;
	}

	/**
	 * Return the subscribed events.
	 *
	 * @return array<string, string|array>
	 */
	public static function get_subscribed_events(): array {
		return [
			'init'                           => 'register_oauth_rewrite_rules',
			'query_vars'                     => 'add_oauth_query_vars',
			'template_redirect'              => 'handle_oauth_request',
			'wp_delete_application_password' => [ 'purge_refresh_jti_meta', 10, 2 ],
			'rocket_activation'              => [ 'on_activation', 20 ],
			'rocket_deactivation'            => 'on_deactivation',
		];
	}

	/**
	 * Register WordPress rewrite rules for all four OAuth endpoints.
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

	/**
	 * Dispatch an incoming OAuth endpoint request to the appropriate handler.
	 *
	 * @return void
	 */
	public function handle_oauth_request(): void {
		$endpoint = (string) get_query_var( self::OAUTH_QUERY_VAR, '' );

		if ( '' === $endpoint ) {
			return;
		}

		switch ( $endpoint ) {
			case 'authorize':
				$this->authorize_endpoint->handle_request();
				break;
			case 'authorize-callback':
				$this->authorize_callback->handle_request();
				break;
			case 'consent':
				$this->consent_endpoint->handle_request();
				break;
			case 'revoke':
				$this->revoke_endpoint->handle_request();
				break;
			case 'token':
				$this->token_endpoint->handle_request();
				break;
			default:
				status_header( 404 );
				wp_die( esc_html__( 'Unknown OAuth endpoint.', 'rocket' ), '', [ 'response' => 404 ] );
		}
	}

	/**
	 * Remove the refresh-token rotation marker when a session's Application
	 * Password is deleted.
	 *
	 * @param int                  $user_id WordPress user ID.
	 * @param array<string, mixed> $item    The Application Password record being deleted.
	 * @return void
	 */
	public function purge_refresh_jti_meta( $user_id, $item ): void {
		$this->token_endpoint->purge_refresh_jti_meta( (int) $user_id, (array) $item );
	}

	/**
	 * Called during plugin activation to initialise the OAuth layer.
	 *
	 * Ensures the JWT signing secret exists, registers all rewrite rules,
	 * and flushes them so OAuth endpoints are immediately reachable.
	 *
	 * @return void
	 */
	public function on_activation(): void {
		SecretManager::ensure_secret();
		$this->register_oauth_rewrite_rules();
		flush_rewrite_rules();
	}

	/**
	 * Called during plugin deactivation to remove the OAuth rewrite rules.
	 *
	 * @return void
	 */
	public function on_deactivation(): void {
		flush_rewrite_rules();
	}
}
