<?php
/**
 * Consent Endpoint.
 *
 * Handles POST /oauth/consent — the form submission from the consent screen
 * rendered by AuthorizeCallback.  Verifies the WordPress nonce, looks up and
 * consumes the state transient, then either issues an auth code (Allow) or
 * redirects back to the client with error=access_denied (Deny).
 *
 * redirect_uri is always read from the server-side state transient, never from
 * $_POST, so it cannot be manipulated by the user or a third party.
 */

declare(strict_types=1);

namespace WP_Rocket\Engine\MCP\Auth;

class ConsentEndpoint {
	/**
	 * Auth-code transient TTL (seconds).  Codes are single-use; the transient
	 * is deleted immediately on redemption at the token endpoint.
	 */
	const CODE_TTL = 60;

	/**
	 * Handle the consent form POST.
	 *
	 * @return void
	 */
	public function handle_request(): void {
		$request_method = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '';

		if ( 'POST' !== $request_method ) {
			McpLogger::log( 'CONSENT', 'rejected: wrong method', [ 'method' => $request_method ] );
			wp_die( esc_html__( 'Method not allowed.', 'rocket' ), esc_html__( 'OAuth Error', 'rocket' ), [ 'response' => 405 ] );
		}

		if ( ! is_user_logged_in() ) {
			McpLogger::log( 'CONSENT', 'rejected: user not logged in' );
			wp_die( esc_html__( 'You must be logged in to authorise an MCP session.', 'rocket' ), esc_html__( 'OAuth Error', 'rocket' ), [ 'response' => 401 ] );
		}

		$state  = sanitize_text_field( wp_unslash( $_POST['state'] ?? '' ) );
		$action = sanitize_text_field( wp_unslash( $_POST['mcp_action'] ?? '' ) );

		if ( '' === $state ) {
			McpLogger::log( 'CONSENT', 'rejected: missing state' );
			wp_die( esc_html__( 'Missing state parameter.', 'rocket' ), esc_html__( 'OAuth Error', 'rocket' ), [ 'response' => 400 ] );
		}

		// Nonce verification (CSRF protection) — must happen before consuming the transient.
		check_admin_referer( 'mcp_consent_' . $state, 'mcp_consent_nonce' );

		$state_key  = 'mcp_oauth_state_' . $state;
		$state_data = get_transient( $state_key );

		if ( false === $state_data || ! is_array( $state_data ) ) {
			McpLogger::log( 'CONSENT', 'rejected: state transient not found or expired', [ 'state' => $state ] );
			wp_die( esc_html__( 'Your session has expired. Please restart the authorization flow.', 'rocket' ), esc_html__( 'OAuth Error', 'rocket' ), [ 'response' => 400 ] );
		}

		// Atomically consume the state — one-time use only. delete_transient()
		// returns true for a single caller when requests race, so a double
		// submission cannot mint two auth codes from one consent.
		if ( ! delete_transient( $state_key ) ) {
			McpLogger::log( 'CONSENT', 'rejected: state already consumed (concurrent submission)', [ 'state' => $state ] );
			wp_die( esc_html__( 'Your session has expired. Please restart the authorization flow.', 'rocket' ), esc_html__( 'OAuth Error', 'rocket' ), [ 'response' => 400 ] );
		}

		$redirect_uri = (string) ( $state_data['redirect_uri'] ?? '' );
		$user_id      = get_current_user_id();

		if ( 'allow' !== $action ) {
			McpLogger::log(
				'CONSENT',
				'user denied access',
				[
					'user_id'      => $user_id,
					'client_id'    => $state_data['client_id'] ?? '',
					'mcp_action'   => $action,
					'redirect_uri' => $redirect_uri,
				]
			);

			wp_redirect( // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- redirecting to the client's registered redirect_uri sourced from the server-side state transient, not user input.
				add_query_arg(
					[
						'error' => 'access_denied',
						'state' => $state,
					],
					$redirect_uri
				)
			);
			exit;
		}

		// User allowed — issue a single-use auth code.
		$auth_code = bin2hex( random_bytes( 32 ) );

		set_transient(
			'mcp_oauth_code_' . $auth_code,
			[
				'user_id'        => $user_id,
				'client_id'      => $state_data['client_id'] ?? '',
				'client_name'    => $state_data['client_name'] ?? '',
				'code_challenge' => $state_data['code_challenge'] ?? '',
				'redirect_uri'   => $redirect_uri,
			],
			self::CODE_TTL
		);

		McpLogger::log(
			'CONSENT',
			'user granted access, auth code issued',
			[
				'user_id'      => $user_id,
				'client_id'    => $state_data['client_id'] ?? '',
				'redirect_uri' => $redirect_uri,
				'code_ttl_s'   => self::CODE_TTL,
			]
		);

		wp_redirect( // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- redirecting to the client's registered redirect_uri sourced from the server-side state transient, not user input.
			add_query_arg(
				[
					'code'  => $auth_code,
					'state' => $state,
				],
				$redirect_uri
			)
		);
		exit;
	}
}
