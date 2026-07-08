<?php
/**
 * Token Revocation Endpoint (RFC 7009).
 *
 * Handles POST /oauth/revoke.  Accepts an access or refresh JWT, verifies its
 * signature (ignoring expiry per spec), then deletes the WordPress Application
 * Password that anchors the session.  Because OAuthHttpTransport checks the
 * Application Password on every request, deletion immediately invalidates all
 * outstanding tokens for that session — no token store is needed.
 *
 * RFC 7009 §2.2 requires HTTP 200 even for unrecognisable or already-revoked
 * tokens; the response body is an empty JSON object `{}`.
 */

declare(strict_types=1);

namespace WP_Rocket\Engine\MCP\Auth;

/**
 * Revoke Endpoint.
 */
class RevokeEndpoint {
	use ParseBodyTrait;

	/**
	 * Handle the revocation request.
	 *
	 * @return void
	 */
	public function handle_request(): void {
		$request_method = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '';

		McpLogger::log(
			'REVOKE',
			'revocation request received',
			[
				'method'      => $request_method,
				'remote_addr' => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
				'user_agent'  => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
			]
		);

		if ( 'POST' !== $request_method ) {
			McpLogger::log( 'REVOKE', 'rejected: wrong method', [ 'method' => $request_method ] );
			$this->send_error( 405, 'invalid_request', 'Method not allowed.' );
			return;
		}

		$body            = $this->parse_body();
		$token           = sanitize_text_field( $body['token'] ?? '' );
		$client_id_param = esc_url_raw( $body['client_id'] ?? '' );

		if ( '' === $token ) {
			McpLogger::log( 'REVOKE', 'rejected: missing token parameter' );
			$this->send_error( 400, 'invalid_request', 'token is required.' );
			return;
		}

		$secret = SecretManager::get_secret();

		// Decode without expiry check — RFC 7009 requires revoking even expired tokens.
		$claims = JWT::decode( $token, $secret, false );

		if ( null === $claims ) {
			// Invalid signature or malformed token — return success per RFC 7009 §2.2.
			McpLogger::log( 'REVOKE', 'no-op: token not recognised (invalid signature or format)' );
			$this->send_success();
			return;
		}

		$user_id       = (int) ( $claims['sub'] ?? 0 );
		$app_pass_uuid = (string) ( $claims['app_pass_id'] ?? '' );

		if ( 0 === $user_id || '' === $app_pass_uuid ) {
			McpLogger::log( 'REVOKE', 'no-op: token missing sub or app_pass_id claims' );
			$this->send_success();
			return;
		}

		// Client binding check (RFC 7009 §2.1): if the caller supplied a client_id and
		// the token carries one, they must match.  A mismatch silently succeeds — no
		// Application Password is deleted and no information about token ownership is
		// leaked to the caller.
		$token_client_id = (string) ( $claims['client_id'] ?? '' );
		if ( '' !== $client_id_param && '' !== $token_client_id && $client_id_param !== $token_client_id ) {
			McpLogger::log(
				'REVOKE',
				'no-op: client_id mismatch',
				[
					'param_client_id' => $client_id_param,
					'token_client_id' => $token_client_id,
					'user_id'         => $user_id,
				]
			);
			$this->send_success();
			return;
		}

		\WP_Application_Passwords::delete_application_password( $user_id, $app_pass_uuid );

		McpLogger::log(
			'REVOKE',
			'session revoked',
			[
				'user_id'       => $user_id,
				'app_pass_uuid' => $app_pass_uuid,
				'client_id'     => $token_client_id,
				'token_type'    => isset( $claims['type'] ) && 'refresh' === $claims['type'] ? 'refresh' : 'access',
			]
		);

		$this->send_success();
	}

	/**
	 * Send a successful revocation response (HTTP 200, empty JSON object).
	 *
	 * @return void
	 */
	private function send_success(): void {
		nocache_headers();
		wp_send_json( new \stdClass() );
	}

	/**
	 * Send a JSON error response and exit.
	 *
	 * @param int    $status      HTTP status code.
	 * @param string $error       OAuth error code.
	 * @param string $description Optional human-readable description.
	 * @return void
	 */
	private function send_error( int $status, string $error, string $description = '' ): void {
		status_header( $status );
		nocache_headers();
		$body = [ 'error' => $error ];
		if ( '' !== $description ) {
			$body['error_description'] = $description;
		}
		wp_send_json( $body );
	}
}
