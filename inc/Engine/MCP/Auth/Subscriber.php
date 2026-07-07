<?php
/**
 * MCP Auth Subscriber.
 *
 * Wires the OAuth 2.1 endpoint routing via the WP Rocket event manager
 * instead of directly calling add_action / add_filter. Plugin lifecycle
 * (activation) is handled separately by Rewrite and SecretManager, which
 * implement ActivationInterface.
 */

declare( strict_types=1 );

namespace WP_Rocket\Engine\MCP\Auth;

use WP_Rocket\Engine\MCP\Context;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Registers OAuth endpoint routing callbacks.
 */
class Subscriber implements Subscriber_Interface {

	/**
	 * OAuth rewrite rules and query var registration.
	 *
	 * @var Rewrite
	 */
	private Rewrite $rewrite;

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
	 * OAuth server context.
	 *
	 * @var Context
	 */
	private Context $context;

	/**
	 * Constructor.
	 *
	 * @param Rewrite           $rewrite             OAuth rewrite rules and query var registration.
	 * @param AuthorizeEndpoint $authorize_endpoint  Authorization endpoint.
	 * @param AuthorizeCallback $authorize_callback  Authorization callback.
	 * @param TokenEndpoint     $token_endpoint      Token endpoint.
	 * @param ConsentEndpoint   $consent_endpoint    Consent endpoint.
	 * @param RevokeEndpoint    $revoke_endpoint     Revocation endpoint.
	 * @param Context           $context             OAuth server context.
	 */
	public function __construct(
		Rewrite $rewrite,
		AuthorizeEndpoint $authorize_endpoint,
		AuthorizeCallback $authorize_callback,
		TokenEndpoint $token_endpoint,
		ConsentEndpoint $consent_endpoint,
		RevokeEndpoint $revoke_endpoint,
		Context $context
	) {
		$this->rewrite            = $rewrite;
		$this->authorize_endpoint = $authorize_endpoint;
		$this->authorize_callback = $authorize_callback;
		$this->token_endpoint     = $token_endpoint;
		$this->consent_endpoint   = $consent_endpoint;
		$this->revoke_endpoint    = $revoke_endpoint;
		$this->context            = $context;
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
		];
	}

	/**
	 * Register WordPress rewrite rules for all five OAuth endpoints.
	 *
	 * Called on the 'init' action (normal page load). Activation registers
	 * the same rules independently via Rewrite::activate().
	 *
	 * @return void
	 */
	public function register_oauth_rewrite_rules(): void {
		if ( ! $this->context->is_enabled() ) {
			return;
		}

		$this->rewrite->register_oauth_rewrite_rules();
	}

	/**
	 * Add the OAuth query var to WordPress's list of recognised vars.
	 *
	 * @param string[] $vars Existing query vars.
	 * @return string[] Modified list.
	 */
	public function add_oauth_query_vars( array $vars ): array {
		return $this->rewrite->add_oauth_query_vars( $vars );
	}

	/**
	 * Dispatch an incoming OAuth endpoint request to the appropriate handler.
	 *
	 * @return void
	 */
	public function handle_oauth_request(): void {
		$endpoint = (string) get_query_var( Rewrite::OAUTH_QUERY_VAR, '' );

		if ( '' === $endpoint ) {
			return;
		}

		if ( ! $this->context->is_enabled() ) {
			$this->force_404();
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
	 * Force a clean 404 response.
	 *
	 * Used when a stale rewrite rule still routes a request to this endpoint
	 * after the OAuth server has been disabled, before rewrite rules have
	 * been flushed. Without this, WordPress's main query would fall through
	 * to the homepage instead of returning a 404.
	 *
	 * @return void
	 */
	private function force_404(): void {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
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
}
