<?php
/**
 * MCP OAuth HTTP Transport.
 *
 * Custom transport that validates a JWT Bearer token before delegating to the
 * standard MCP HttpRequestHandler. Auth is performed inside handle_request()
 * (rather than check_permission()) so we can return a proper 401 response with
 * a WWW-Authenticate challenge header — something WordPress's permission-callback
 * mechanism cannot produce.
 */

declare( strict_types=1 );

namespace WP_Rocket\Engine\MCP\Transport;

use WP\MCP\Transport\Contracts\McpRestTransportInterface;
use WP\MCP\Transport\Infrastructure\HttpRequestContext;
use WP\MCP\Transport\Infrastructure\HttpRequestHandler;
use WP\MCP\Transport\Infrastructure\McpTransportContext;
use WP\MCP\Transport\Infrastructure\McpTransportHelperTrait;
use WP_Rocket\Engine\MCP\Auth\JWT;
use WP_Rocket\Engine\MCP\Auth\McpLogger;
use WP_Rocket\Engine\MCP\Auth\SecretManager;

/**
 * OAuth HTTP Transport for the WP Rocket MCP server.
 */
class OAuthHttpTransport implements McpRestTransportInterface {
	use McpTransportHelperTrait;

	/**
	 * Delegates all MCP protocol handling (sessions, JSON-RPC, etc.).
	 *
	 * @var HttpRequestHandler
	 */
	protected HttpRequestHandler $request_handler;

	/**
	 * Constructor — registers the REST route on rest_api_init.
	 *
	 * @param McpTransportContext $transport_context Transport context provided by the MCP adapter.
	 */
	public function __construct( McpTransportContext $transport_context ) {
		$this->request_handler = new HttpRequestHandler( $transport_context );
		add_action( 'rest_api_init', [ $this, 'register_routes' ], 16 );
	}

	/**
	 * Register the WP Rocket MCP REST route.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		$server = $this->request_handler->transport_context->mcp_server;

		register_rest_route(
			$server->get_server_route_namespace(),
			$server->get_server_route(),
			[
				'methods'             => [ 'POST', 'GET', 'DELETE' ],
				'callback'            => [ $this, 'handle_request' ],
				'permission_callback' => [ $this, 'check_permission' ],
			]
		);
	}

	/**
	 * Permission callback — always returns true.
	 *
	 * Authentication is performed in handle_request() so we can return a 401
	 * response with the required WWW-Authenticate header. WordPress's built-in
	 * permission-callback mechanism only allows bool|\WP_Error returns and
	 * cannot set custom response headers.
	 *
	 * @param \WP_REST_Request $request Incoming request.
	 * @return true
	 */
	public function check_permission( \WP_REST_Request $request ): bool {
		return true;
	}

	/**
	 * Handle an incoming MCP request.
	 *
	 * Validates the JWT Bearer token first. On failure, returns a 401 response
	 * with a WWW-Authenticate challenge so OAuth 2.1 clients know how to
	 * authenticate. On success, establishes the WordPress user context and
	 * delegates to HttpRequestHandler for full MCP protocol compliance.
	 *
	 * @param \WP_REST_Request $request Incoming REST request.
	 * @return \WP_REST_Response MCP response.
	 */
	public function handle_request( \WP_REST_Request $request ): \WP_REST_Response {
		$user_or_error = $this->validate_bearer_token( $request );

		if ( is_wp_error( $user_or_error ) ) {
			return $this->build_auth_error_response( $user_or_error );
		}

		McpLogger::log(
			'TRANSPORT',
			'JWT validated, delegating to MCP handler',
			[
				'user_id' => $user_or_error->ID,
				'method'  => $request->get_method(),
			],
			true
		);

		$context = new HttpRequestContext( $request );
		return $this->request_handler->handle_request( $context );
	}

	/**
	 * Validate the JWT Bearer token from the Authorization header.
	 *
	 * Extracts the token, verifies the JWT signature and expiry, checks audience,
	 * confirms the anchoring Application Password has not been revoked, and calls
	 * wp_set_current_user() on success.
	 *
	 * @param \WP_REST_Request $request Incoming REST request.
	 * @return \WP_User|\WP_Error Authenticated user, or an error.
	 */
	private function validate_bearer_token( \WP_REST_Request $request ) {
		$authorization = $request->get_header( 'Authorization' );
		$route         = $request->get_route();
		$method        = $request->get_method();

		McpLogger::log(
			'TRANSPORT',
			'validate_bearer_token called',
			[
				'method'  => $method,
				'route'   => $route,
				'headers' => McpLogger::safe_request_headers(),
				'body'    => McpLogger::safe_request_body(),
			],
			true
		);

		if ( empty( $authorization ) || 0 !== strpos( $authorization, 'Bearer ' ) ) {
			McpLogger::log(
				'TRANSPORT',
				'unauthenticated: no Bearer token',
				[
					'authorization_header' => $authorization ? substr( $authorization, 0, 20 ) . '...' : '(empty)',
				]
			);
			return $this->unauthenticated_error();
		}

		$token  = substr( $authorization, 7 );
		$secret = SecretManager::get_secret();
		$claims = JWT::decode( $token, $secret );

		if ( null === $claims ) {
			McpLogger::log( 'TRANSPORT', 'rejected: JWT decode failed (signature invalid or token expired)' );
			return $this->unauthenticated_error( 'invalid_token', 'JWT signature invalid or token expired.' );
		}

		// Audience must match the WP Rocket MCP REST endpoint.
		$expected_aud = get_rest_url( null, 'mcp/mcp-oauth-server' );
		$token_aud    = $claims['aud'] ?? '';

		if ( $token_aud !== $expected_aud ) {
			McpLogger::log(
				'TRANSPORT',
				'rejected: JWT audience mismatch',
				[
					'token_aud'    => $token_aud,
					'expected_aud' => $expected_aud,
				]
			);
			return $this->unauthenticated_error( 'invalid_token', 'JWT audience mismatch.' );
		}

		// Issuer must be this site. A staging clone sharing the same JWT secret
		// would otherwise allow cross-site token replay — the audience already
		// embeds the site URL, but verifying iss explicitly keeps this check in
		// step with the refresh-token flow in TokenEndpoint::handle_refresh_token().
		// home_url() matches what TokenEndpoint mints into iss and the home-based
		// base get_rest_url() uses for aud.
		$expected_iss = home_url();
		$token_iss    = (string) ( $claims['iss'] ?? '' );

		if ( $token_iss !== $expected_iss ) {
			McpLogger::log(
				'TRANSPORT',
				'rejected: JWT issuer mismatch',
				[
					'token_iss'    => $token_iss,
					'expected_iss' => $expected_iss,
				]
			);
			return $this->unauthenticated_error( 'invalid_token', 'JWT issuer mismatch.' );
		}

		$user_id       = (int) ( $claims['sub'] ?? 0 );
		$app_pass_uuid = (string) ( $claims['app_pass_id'] ?? '' );

		if ( 0 === $user_id || '' === $app_pass_uuid ) {
			McpLogger::log(
				'TRANSPORT',
				'rejected: malformed JWT claims',
				[
					'has_sub'         => isset( $claims['sub'] ) ? 'yes' : 'no',
					'has_app_pass_id' => isset( $claims['app_pass_id'] ) ? 'yes' : 'no',
					'claims_keys'     => array_keys( $claims ),
				]
			);
			return $this->unauthenticated_error( 'invalid_token', 'Malformed JWT claims.' );
		}

		// Application Password revocation check.
		$app_pass = \WP_Application_Passwords::get_user_application_password( $user_id, $app_pass_uuid );

		if ( ! is_array( $app_pass ) ) {
			McpLogger::log(
				'TRANSPORT',
				'rejected: Application Password revoked or not found',
				[
					'user_id'       => $user_id,
					'app_pass_uuid' => $app_pass_uuid,
				]
			);
			return $this->unauthenticated_error( 'invalid_token', 'MCP session has been revoked.' );
		}

		$user = get_user_by( 'id', $user_id );

		if ( false === $user ) {
			McpLogger::log( 'TRANSPORT', 'rejected: user not found', [ 'user_id' => $user_id ] );
			return $this->unauthenticated_error( 'invalid_token', 'User not found.' );
		}

		// Set the current user so that get_current_user_id() returns the correct
		// user for any library code that runs after authentication (e.g.
		// HttpSessionValidator::create_session() in wordpress/mcp-adapter).
		wp_set_current_user( $user_id );

		McpLogger::log(
			'TRANSPORT',
			'authentication successful',
			[
				'user_id'       => $user_id,
				'user_login'    => $user->user_login,
				'app_pass_uuid' => $app_pass_uuid,
				'token_exp'     => $claims['exp'] ?? 'unknown',
				'token_scope'   => $claims['scope'] ?? 'unknown',
			]
		);

		return $user;
	}

	/**
	 * Build a WP_Error representing a 401 Unauthorized response.
	 *
	 * Includes a WWW-Authenticate Bearer challenge so OAuth 2.1 clients know
	 * where to find the protected-resource metadata.
	 *
	 * @param string $code        OAuth error code (default 'unauthorized').
	 * @param string $description Human-readable message.
	 * @return \WP_Error
	 */
	private function unauthenticated_error( string $code = 'unauthorized', string $description = '' ): \WP_Error {
		// home_url(): the .well-known document is served via a rewrite rule, so the
		// realm and resource_metadata URL must use the Site Address base.
		$base_url = home_url();

		$www_auth = sprintf(
			'Bearer realm="%s", resource_metadata="%s/.well-known/oauth-protected-resource"',
			esc_url( $base_url ),
			esc_url( $base_url )
		);

		if ( '' !== $description ) {
			$www_auth .= sprintf( ', error="%s", error_description="%s"', $code, $description );
		}

		return new \WP_Error(
			'mcp_unauthorized',
			'' !== $description ? $description : __( 'MCP authentication required.', 'rocket' ),
			[
				'status'           => 401,
				'WWW-Authenticate' => $www_auth,
			]
		);
	}

	/**
	 * Build a 401 REST response with a WWW-Authenticate header.
	 *
	 * @param \WP_Error $error Authentication error.
	 * @return \WP_REST_Response
	 */
	private function build_auth_error_response( \WP_Error $error ): \WP_REST_Response {
		$error_data = $error->get_error_data();
		$www_auth   = is_array( $error_data ) ? ( $error_data['WWW-Authenticate'] ?? '' ) : '';
		$status     = is_array( $error_data ) ? (int) ( $error_data['status'] ?? 401 ) : 401;

		$response = new \WP_REST_Response(
			[
				'code'    => $error->get_error_code(),
				'message' => $error->get_error_message(),
				'data'    => [ 'status' => $status ],
			],
			$status
		);

		if ( '' !== $www_auth ) {
			$response->header( 'WWW-Authenticate', $www_auth );
		}

		McpLogger::log(
			'TRANSPORT',
			'JWT validation failed, returning 401',
			[
				'error_code'   => $error->get_error_code(),
				'has_www_auth' => '' !== $www_auth ? 'yes' : 'no',
			]
		);

		return $response;
	}
}
