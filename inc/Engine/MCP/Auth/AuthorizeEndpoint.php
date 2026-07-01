<?php
/**
 * Authorization Endpoint.
 *
 * Handles GET /oauth/authorize — validates the PKCE parameters, stores the
 * challenge in a 60-second transient, then redirects the browser to the
 * WordPress login form.  After successful login WordPress delivers the user
 * to /oauth/authorize-callback where the auth code is issued.
 */

declare(strict_types=1);

namespace WP_Rocket\Engine\MCP\Auth;

/**
 * Authorize Endpoint.
 */
class AuthorizeEndpoint {
	/**
	 * Transient TTL for the state parameter (seconds).
	 */
	const STATE_TTL = 60;

	/**
	 * CIMD resolver used to dereference and validate the client_id URL.
	 *
	 * @var CimdResolver
	 */
	private CimdResolver $resolver;

	/**
	 * Constructor.
	 *
	 * @param CimdResolver $resolver CIMD resolver.
	 */
	public function __construct( CimdResolver $resolver ) {
		$this->resolver = $resolver;
	}

	/**
	 * Handle an authorization request.
	 *
	 * @return void
	 */
	public function handle_request(): void {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- OAuth 2.1 authorization request from an external client; CSRF protection is provided by the state parameter and PKCE, not a WP nonce.
		$client_id             = esc_url_raw( wp_unslash( $_GET['client_id'] ?? '' ) );
		$redirect_uri          = esc_url_raw( wp_unslash( $_GET['redirect_uri'] ?? '' ) );
		$response_type         = sanitize_text_field( wp_unslash( $_GET['response_type'] ?? '' ) );
		$code_challenge        = sanitize_text_field( wp_unslash( $_GET['code_challenge'] ?? '' ) );
		$code_challenge_method = sanitize_text_field( wp_unslash( $_GET['code_challenge_method'] ?? '' ) );
		$state                 = sanitize_text_field( wp_unslash( $_GET['state'] ?? '' ) );
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		McpLogger::log(
			'AUTHORIZE',
			'authorization request received',
			[
				'response_type'         => $response_type,
				'client_id'             => $client_id,
				'redirect_uri'          => $redirect_uri,
				'code_challenge_method' => $code_challenge_method,
				'has_code_challenge'    => '' !== $code_challenge ? 'yes' : 'no',
				'has_state'             => '' !== $state ? 'yes' : 'no',
				'remote_addr'           => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
			]
		);

		// Validate the client and redirect_uri BEFORE using redirect_uri in any redirect.
		// Per OAuth 2.1 §7.5.2 and RFC 6749 §10.15, the AS MUST NOT redirect to a URI
		// that has not been positively validated against a registered client.

		if ( '' === $client_id ) {
			McpLogger::log( 'AUTHORIZE', 'rejected: missing client_id' );
			wp_die( esc_html__( 'client_id is required.', 'rocket' ), esc_html__( 'OAuth Error', 'rocket' ), [ 'response' => 400 ] );
		}

		$client = $this->resolver->resolve( $client_id );
		if ( null === $client ) {
			McpLogger::log( 'AUTHORIZE', 'rejected: client_id could not be resolved via CIMD', [ 'client_id' => $client_id ] );
			wp_die( esc_html__( 'Unknown OAuth client.', 'rocket' ), esc_html__( 'OAuth Error', 'rocket' ), [ 'response' => 400 ] );
		}

		if ( empty( $client['verified'] ) ) {
			McpLogger::log( 'AUTHORIZE', 'rejected: client not a verified publisher', [ 'client_id' => $client_id ] );
			wp_die( esc_html__( 'This OAuth client is not a verified publisher.', 'rocket' ), esc_html__( 'OAuth Error', 'rocket' ), [ 'response' => 400 ] );
		}

		if ( '' === $redirect_uri ) {
			McpLogger::log( 'AUTHORIZE', 'rejected: missing redirect_uri' );
			wp_die( esc_html__( 'redirect_uri is required.', 'rocket' ), esc_html__( 'OAuth Error', 'rocket' ), [ 'response' => 400 ] );
		}

		if ( ! $this->redirect_uri_matches( $redirect_uri, $client['redirect_uris'] ) ) {
			McpLogger::log(
				'AUTHORIZE',
				'rejected: redirect_uri mismatch',
				[
					'provided'   => $redirect_uri,
					'registered' => $client['redirect_uris'],
				]
			);
			wp_die( esc_html__( 'redirect_uri does not match registered value.', 'rocket' ), esc_html__( 'OAuth Error', 'rocket' ), [ 'response' => 400 ] );
		}

		// redirect_uri is now validated — remaining errors may safely redirect to it.

		if ( 'code' !== $response_type ) {
			McpLogger::log( 'AUTHORIZE', 'rejected: unsupported response_type', [ 'response_type' => $response_type ] );
			$this->send_error( $redirect_uri, 'unsupported_response_type', $state );
			return;
		}

		if ( '' === $code_challenge || 'S256' !== $code_challenge_method ) {
			McpLogger::log(
				'AUTHORIZE',
				'rejected: missing or invalid PKCE params',
				[
					'has_code_challenge'    => '' !== $code_challenge ? 'yes' : 'no',
					'code_challenge_method' => $code_challenge_method,
				]
			);
			$this->send_error( $redirect_uri, 'invalid_request', $state );
			return;
		}

		// OAuth 2.1 §4.1.1 requires state; reject rather than generate silently.
		// A server-generated state never reaches the client before the redirect,
		// so the client cannot validate it on return — providing no CSRF protection.
		if ( '' === $state ) {
			McpLogger::log( 'AUTHORIZE', 'rejected: state parameter is required' );
			$this->send_error( $redirect_uri, 'invalid_request', '' );
			return;
		}

		// Persist the validated client display data alongside the PKCE state so the
		// consent screen can be rendered after login without a second CIMD fetch.
		set_transient(
			'mcp_oauth_state_' . $state,
			[
				'client_id'             => $client_id,
				'client_name'           => (string) ( $client['client_name'] ?? '' ),
				'client_uri'            => (string) ( $client['client_uri'] ?? '' ),
				// Already guaranteed truthy - the 'client not a verified publisher' guard above exits otherwise.
				'verified'              => true,
				'publisher'             => (string) ( $client['publisher'] ?? '' ),
				'redirect_uri'          => $redirect_uri,
				'code_challenge'        => $code_challenge,
				'code_challenge_method' => $code_challenge_method,
				'state'                 => $state,
			],
			self::STATE_TTL
		);

		// home_url(): the callback is a rewrite endpoint served from the Site Address,
		// so it must match home_url() and not get_site_url() on split-directory installs.
		$callback_url = add_query_arg( 'state', rawurlencode( $state ), home_url( '/oauth/authorize-callback' ) );
		$login_url    = wp_login_url( $callback_url );

		McpLogger::log(
			'AUTHORIZE',
			'redirecting to login',
			[
				'state'        => $state,
				'callback_url' => $callback_url,
			]
		);

		wp_redirect( $login_url ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
		exit;
	}

	/**
	 * Determine whether a provided redirect_uri matches a registered one.
	 *
	 * Non-loopback clients require an exact match.  Loopback clients (native
	 * apps per RFC 8252) are matched port-agnostically: the ephemeral port
	 * varies per session, so scheme, host, and path must match but the port is
	 * ignored.  The exemption is constrained to the literal loopback hosts over
	 * http so it cannot widen open-redirect exposure for normal clients.
	 *
	 * @param string   $provided   The redirect_uri supplied in the request.
	 * @param string[] $registered The redirect URIs from the client metadata.
	 * @return bool
	 */
	private function redirect_uri_matches( string $provided, array $registered ): bool {
		if ( in_array( $provided, $registered, true ) ) {
			return true;
		}

		$provided_parts = wp_parse_url( $provided );
		if ( ! is_array( $provided_parts ) || ! $this->is_loopback( $provided_parts ) ) {
			return false;
		}

		foreach ( $registered as $candidate ) {
			$candidate_parts = wp_parse_url( (string) $candidate );
			if ( ! is_array( $candidate_parts ) || ! $this->is_loopback( $candidate_parts ) ) {
				continue;
			}

			if (
				( $provided_parts['scheme'] ?? '' ) === ( $candidate_parts['scheme'] ?? '' )
				&& ( $provided_parts['host'] ?? '' ) === ( $candidate_parts['host'] ?? '' )
				&& ( $provided_parts['path'] ?? '' ) === ( $candidate_parts['path'] ?? '' )
			) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Whether a parsed URL points at a loopback address over http.
	 *
	 * Covers IPv4 (127.0.0.1), hostname (localhost), and IPv6 (::1) loopback
	 * addresses per RFC 8252 §8.3.  Only plain HTTP is permitted — HTTPS
	 * loopback is not exempted to avoid widening open-redirect exposure for
	 * normal (non-native-app) clients.  wp_parse_url() strips the brackets
	 * from IPv6 literals, so the host value is '::1', not '[::1]'.
	 *
	 * @param array<string, mixed> $parts Parsed URL components from wp_parse_url().
	 * @return bool
	 */
	private function is_loopback( array $parts ): bool {
		$scheme = (string) ( $parts['scheme'] ?? '' );
		$host   = (string) ( $parts['host'] ?? '' );

		return 'http' === $scheme && in_array( $host, [ '127.0.0.1', 'localhost', '::1' ], true );
	}

	/**
	 * Redirect the client to redirect_uri with an error parameter.
	 *
	 * @param string $redirect_uri Destination URI (may be empty on early failure).
	 * @param string $error        OAuth error code.
	 * @param string $state        State token echoed back to the client.
	 * @return void
	 */
	private function send_error( string $redirect_uri, string $error, string $state ): void {
		if ( '' !== $redirect_uri ) {
			$params = [ 'error' => $error ];
			if ( '' !== $state ) {
				$params['state'] = $state;
			}
			wp_redirect( add_query_arg( $params, $redirect_uri ) ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- redirecting to the client's own registered redirect_uri, already validated against the CIMD allowlist; not a same-site redirect.
			exit;
		}

		wp_die( esc_html( $error ), esc_html__( 'OAuth Error', 'rocket' ), [ 'response' => 400 ] );
	}
}
