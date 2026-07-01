<?php
/**
 * Token Endpoint.
 *
 * Handles POST /oauth/token for two grant types:
 *   - authorization_code: PKCE verification → create Application Password → issue JWT pair.
 *   - refresh_token:      verify refresh JWT → revocation check → issue new access JWT.
 *
 * Raw Application Passwords are never stored and are discarded immediately
 * after creation.  Only the UUID is retained inside the JWT claims for
 * revocation checking.
 */

declare(strict_types=1);

namespace WP_Rocket\Engine\MCP\Auth;

class TokenEndpoint {
	use ParseBodyTrait;

	/**
	 * User-meta key prefix storing the currently valid refresh-token id (jti)
	 * for a session. The session is identified by its Application Password UUID,
	 * so the full key is REFRESH_JTI_META_PREFIX . $app_pass_uuid.
	 */
	const REFRESH_JTI_META_PREFIX = 'mcp_refresh_jti_';

	/**
	 * Maximum number of concurrent MCP sessions (Application Passwords) retained
	 * per user per client. Every successful code exchange mints a new Application
	 * Password, so a client that re-runs the authorize→token flow repeatedly would
	 * otherwise accumulate unbounded rows — bloating the user's Application
	 * Passwords screen and slowing the per-request revocation lookup in
	 * OAuthHttpTransport, which scans all of the user's Application Passwords. When
	 * a new session would exceed this cap, the oldest sessions for that client are
	 * evicted first.
	 */
	const MAX_SESSIONS_PER_CLIENT = 5;

	/**
	 * Handle the token request.
	 *
	 * @return void
	 */
	public function handle_request(): void {
		$request_method = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '';
		$content_type   = isset( $_SERVER['CONTENT_TYPE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['CONTENT_TYPE'] ) ) : '';

		McpLogger::log(
			'TOKEN',
			'token request received',
			[
				'method'       => $request_method,
				'content_type' => $content_type,
				'remote_addr'  => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
				'user_agent'   => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
				'headers'      => McpLogger::safe_request_headers(),
				'body'         => McpLogger::safe_request_body(),
			]
		);

		if ( 'POST' !== $request_method ) {
			McpLogger::log( 'TOKEN', 'rejected: wrong method', [ 'method' => $request_method ] );
			$this->send_error( 405, 'invalid_request', 'Method not allowed.' );
			return;
		}

		$body = $this->parse_body();

		$grant_type = sanitize_text_field( $body['grant_type'] ?? '' );

		McpLogger::log( 'TOKEN', 'grant_type received', [ 'grant_type' => $grant_type ] );

		if ( 'authorization_code' === $grant_type ) {
			$this->handle_authorization_code( $body );
		} elseif ( 'refresh_token' === $grant_type ) {
			$this->handle_refresh_token( $body );
		} else {
			McpLogger::log( 'TOKEN', 'rejected: unsupported grant_type', [ 'grant_type' => $grant_type ] );
			$this->send_error( 400, 'unsupported_grant_type' );
		}
	}

	/**
	 * Exchange an auth code for a JWT pair.
	 *
	 * @param array<string, mixed> $body Parsed request body.
	 * @return void
	 */
	private function handle_authorization_code( array $body ): void {
		$code          = sanitize_text_field( $body['code'] ?? '' );
		$code_verifier = sanitize_text_field( $body['code_verifier'] ?? '' );
		$redirect_uri  = esc_url_raw( $body['redirect_uri'] ?? '' );

		McpLogger::log(
			'TOKEN',
			'authorization_code exchange: params',
			[
				'has_code'          => '' !== $code ? 'yes' : 'no',
				'has_code_verifier' => '' !== $code_verifier ? 'yes' : 'no',
				'redirect_uri'      => $redirect_uri,
			]
		);

		if ( '' === $code || '' === $code_verifier ) {
			McpLogger::log( 'TOKEN', 'rejected: missing code or code_verifier' );
			$this->send_error( 400, 'invalid_request', 'code and code_verifier are required.' );
			return;
		}

		// Look up the single-use code, then atomically consume it. delete_transient()
		// returns true for only one caller when two requests race for the same code
		// (delete_option / wp_cache_delete report the row/key they actually removed),
		// so a double-submitted or replayed code is rejected here rather than minting
		// a second session.
		$code_key  = 'mcp_oauth_code_' . $code;
		$code_data = get_transient( $code_key );

		if ( false === $code_data || ! is_array( $code_data ) ) {
			McpLogger::log( 'TOKEN', 'rejected: auth code transient missing or expired (60 s window)' );
			$this->send_error( 400, 'invalid_grant', 'Code is invalid or has expired.' );
			return;
		}

		if ( ! delete_transient( $code_key ) ) {
			McpLogger::log( 'TOKEN', 'rejected: auth code already consumed (concurrent redemption)' );
			$this->send_error( 400, 'invalid_grant', 'Code is invalid or has expired.' );
			return;
		}

		// Verify PKCE S256: BASE64URL(SHA256(verifier)) must equal stored challenge.
		$expected = JWT::base64url_encode( hash( 'sha256', $code_verifier, true ) );

		if ( ! hash_equals( (string) $code_data['code_challenge'], $expected ) ) {
			McpLogger::log( 'TOKEN', 'rejected: PKCE code_verifier does not match challenge' );
			$this->send_error( 400, 'invalid_grant', 'PKCE code_verifier does not match challenge.' );
			return;
		}

		// redirect_uri is required whenever it was included in the authorization request
		// (OAuth 2.1 §4.1.3). AuthorizeEndpoint always requires it, so code_data always
		// contains a non-empty value. Exact match is enforced; the client must send the
		// same runtime URI (including ephemeral port for loopback) it used at authorize time.
		if ( $redirect_uri !== (string) $code_data['redirect_uri'] ) {
			McpLogger::log(
				'TOKEN',
				'rejected: redirect_uri missing or does not match authorization request',
				[
					'provided' => $redirect_uri,
					'stored'   => $code_data['redirect_uri'],
				]
			);
			$this->send_error( 400, 'invalid_grant', 'redirect_uri is required and must match the authorization request.' );
			return;
		}

		$user_id   = (int) $code_data['user_id'];
		$client_id = (string) ( $code_data['client_id'] ?? '' );

		// The Application Password name is required by WordPress core and must be
		// non-empty. Fall back to the client host when the CIMD document omitted a
		// client_name so a sparse-but-valid client cannot break the token exchange.
		$client_name = (string) ( $code_data['client_name'] ?? '' );
		if ( '' === $client_name ) {
			$client_name = (string) wp_parse_url( $client_id, PHP_URL_HOST );
		}
		if ( '' === $client_name ) {
			$client_name = 'MCP Client';
		}

		McpLogger::log( 'TOKEN', 'PKCE verified — creating Application Password', [ 'user_id' => $user_id ] );

		// Bound the number of sessions this client can stockpile before adding one more.
		$this->prune_sessions( $user_id, $client_id );

		// Create a WordPress Application Password (raw password is discarded).
		$result = \WP_Application_Passwords::create_new_application_password(
			$user_id,
			[
				'name'   => $client_name,
				'app_id' => $client_id,
			],
		);

		if ( is_wp_error( $result ) ) {
			McpLogger::log(
				'TOKEN',
				'server_error: Application Password creation failed',
				[
					'wp_error_code'    => $result->get_error_code(),
					'wp_error_message' => $result->get_error_message(),
					'user_id'          => $user_id,
				]
			);
			$this->send_error( 500, 'server_error', 'Could not create MCP session.' );
			return;
		}

		// create_new_application_password() returns [raw_password, metadata]. Raw password is discarded.
		$app_pass_uuid = (string) $result[1]['uuid'];

		McpLogger::log(
			'TOKEN',
			'Application Password created — issuing token pair',
			[
				'user_id'       => $user_id,
				'app_pass_uuid' => $app_pass_uuid,
			]
		);

		$this->issue_token_pair( $user_id, $app_pass_uuid, $client_id );
	}

	/**
	 * Refresh an access token using a valid refresh JWT.
	 *
	 * @param array<string, mixed> $body Parsed request body.
	 * @return void
	 */
	private function handle_refresh_token( array $body ): void {
		$refresh_token = sanitize_text_field( $body['refresh_token'] ?? '' );

		McpLogger::log( 'TOKEN', 'refresh_token grant: validating', [ 'has_refresh_token' => '' !== $refresh_token ? 'yes' : 'no' ] );

		if ( '' === $refresh_token ) {
			McpLogger::log( 'TOKEN', 'rejected: refresh_token missing' );
			$this->send_error( 400, 'invalid_request', 'refresh_token is required.' );
			return;
		}

		$secret = SecretManager::get_secret();
		$claims = JWT::decode( $refresh_token, $secret );

		if ( null === $claims || 'refresh' !== ( $claims['type'] ?? '' ) ) {
			McpLogger::log(
				'TOKEN',
				'rejected: refresh token decode failed or wrong type',
				[
					'claims_null' => null === $claims ? 'yes' : 'no',
					'type'        => null !== $claims ? ( $claims['type'] ?? '(missing)' ) : 'n/a',
				]
			);
			$this->send_error( 401, 'invalid_token', 'Refresh token is invalid or expired.' );
			return;
		}

		// Verify the token was issued by this site. A staging clone sharing the same
		// JWT secret would otherwise allow cross-site token replay.
		$token_iss = (string) ( $claims['iss'] ?? '' );
		if ( home_url() !== $token_iss ) {
			McpLogger::log(
				'TOKEN',
				'rejected: refresh token issuer mismatch',
				[
					'token_iss'    => $token_iss,
					'expected_iss' => home_url(),
				]
			);
			$this->send_error( 401, 'invalid_token', 'Refresh token was not issued by this server.' );
			return;
		}

		$user_id       = (int) $claims['sub'];
		$app_pass_uuid = (string) ( $claims['app_pass_id'] ?? '' );
		$client_id     = (string) ( $claims['client_id'] ?? '' );

		McpLogger::log(
			'TOKEN',
			'refresh token decoded — checking revocation',
			[
				'user_id'       => $user_id,
				'app_pass_uuid' => $app_pass_uuid,
			]
			);

		// Revocation check: if the Application Password was deleted the session is gone.
		$app_pass = \WP_Application_Passwords::get_user_application_password( $user_id, $app_pass_uuid );

		if ( ! is_array( $app_pass ) ) {
			// Session is gone; drop the now-orphaned rotation marker too.
			delete_user_meta( $user_id, self::REFRESH_JTI_META_PREFIX . $app_pass_uuid );
			McpLogger::log(
				'TOKEN',
				'rejected: Application Password revoked or not found',
				[
					'user_id'       => $user_id,
					'app_pass_uuid' => $app_pass_uuid,
				]
				);
			$this->send_error( 401, 'invalid_token', 'MCP session has been revoked.' );
			return;
		}

		// Refresh-token rotation / reuse detection (OAuth 2.1 §4.3.1, RFC 6819 §5.2.2.3).
		// Each issued refresh token carries a unique jti; only the most recently
		// issued jti for this session is accepted. Presenting any other (i.e. an
		// already-rotated, previously-consumed) refresh token means the token has
		// leaked and is being replayed — revoke the whole session so the attacker
		// and the legitimate client are both forced to re-authenticate.
		$presented_jti = (string) ( $claims['jti'] ?? '' );
		$current_jti   = (string) get_user_meta( $user_id, self::REFRESH_JTI_META_PREFIX . $app_pass_uuid, true );

		if ( '' === $presented_jti || '' === $current_jti || ! hash_equals( $current_jti, $presented_jti ) ) {
			McpLogger::log(
				'TOKEN',
				'SECURITY: refresh token reuse detected — revoking session',
				[
					'user_id'       => $user_id,
					'app_pass_uuid' => $app_pass_uuid,
					'has_presented' => '' !== $presented_jti ? 'yes' : 'no',
					'has_current'   => '' !== $current_jti ? 'yes' : 'no',
				]
			);
			// Deleting the Application Password fires wp_delete_application_password,
			// which purges the rotation marker via purge_refresh_jti_meta().
			\WP_Application_Passwords::delete_application_password( $user_id, $app_pass_uuid );
			$this->send_error( 401, 'invalid_grant', 'Refresh token has been revoked.' );
			return;
		}

		McpLogger::log(
			'TOKEN',
			'refresh token valid — issuing new token pair',
			[
				'user_id'       => $user_id,
				'app_pass_uuid' => $app_pass_uuid,
			]
			);

		// Issue a new access token and rotate the refresh token: issue_token_pair()
		// mints a fresh jti and overwrites the stored marker, invalidating the
		// refresh token just presented.
		$this->issue_token_pair( $user_id, $app_pass_uuid, $client_id );
	}

	/**
	 * Build and return an access + refresh JWT pair.
	 *
	 * @param int    $user_id       WordPress user ID.
	 * @param string $app_pass_uuid UUID of the Application Password.
	 * @param string $client_id     Client ID URL from the CIMD record.
	 * @return void
	 */
	private function issue_token_pair( int $user_id, string $app_pass_uuid, string $client_id = '' ): void {
		$secret = SecretManager::get_secret();
		// iss is home_url() (the Site Address), matching the base get_rest_url()
		// uses for aud and where the OAuth/.well-known routes are actually served.
		$issuer = home_url();
		$now    = time();
		$aud    = get_rest_url( null, 'mcp/mcp-oauth-server' );

		// Mint a fresh refresh-token id and persist it as the only one accepted
		// for this session. This overwrites any prior marker, so the previously
		// issued refresh token (if any) is invalidated the instant this returns.
		$refresh_jti = bin2hex( random_bytes( 16 ) );
		update_user_meta( $user_id, self::REFRESH_JTI_META_PREFIX . $app_pass_uuid, $refresh_jti );

		$access_payload = [
			'iss'         => $issuer,
			'aud'         => $aud,
			'sub'         => (string) $user_id,
			'app_pass_id' => $app_pass_uuid,
			'client_id'   => $client_id,
			'scope'       => 'mcp',
			'iat'         => $now,
			'exp'         => $now + HOUR_IN_SECONDS,
		];

		$refresh_payload = [
			'iss'         => $issuer,
			'sub'         => (string) $user_id,
			'app_pass_id' => $app_pass_uuid,
			'client_id'   => $client_id,
			'type'        => 'refresh',
			'jti'         => $refresh_jti,
			'iat'         => $now,
			'exp'         => $now + ( 30 * DAY_IN_SECONDS ),
		];

		$access_token  = JWT::encode( $access_payload, $secret );
		$refresh_token = JWT::encode( $refresh_payload, $secret );

		McpLogger::log(
			'TOKEN',
			'token pair issued',
			[
				'user_id'          => $user_id,
				'app_pass_uuid'    => $app_pass_uuid,
				'access_exp'       => $access_payload['exp'],
				'refresh_exp'      => $refresh_payload['exp'],
				'access_token_len' => strlen( $access_token ),
			]
		);

		nocache_headers();
		wp_send_json(
			[
				'access_token'  => $access_token,
				'token_type'    => 'Bearer',
				'expires_in'    => HOUR_IN_SECONDS,
				'refresh_token' => $refresh_token,
				'scope'         => 'mcp',
			]
		);
	}

	/**
	 * Evict the oldest MCP sessions for a user+client so that creating one more
	 * stays within MAX_SESSIONS_PER_CLIENT.
	 *
	 * Only Application Passwords this feature created are considered — they are
	 * matched by the `app_id` we set to the client_id at creation time, so
	 * Application Passwords the user created for other integrations are never
	 * touched. Deleting one fires `wp_delete_application_password`, which removes
	 * its `mcp_refresh_jti_*` meta via purge_refresh_jti_meta(), so no orphaned
	 * rows are left behind.
	 *
	 * @param int    $user_id   WordPress user ID.
	 * @param string $client_id Client ID URL the sessions belong to.
	 * @return void
	 */
	private function prune_sessions( int $user_id, string $client_id ): void {
		if ( '' === $client_id ) {
			return;
		}

		$passwords = \WP_Application_Passwords::get_user_application_passwords( $user_id );

		// Keep only this feature's Application Passwords for this client.
		$ours = array_values(
			array_filter(
				$passwords,
				static function ( $item ) use ( $client_id ) {
					return $item['app_id'] === $client_id;
				}
			)
		);

		// Below the cap: the new session about to be created still fits.
		if ( count( $ours ) < self::MAX_SESSIONS_PER_CLIENT ) {
			return;
		}

		// Oldest first.
		usort(
			$ours,
			static function ( $a, $b ) {
				return $a['created'] <=> $b['created'];
			}
		);

		// Evict enough of the oldest so that, after the new one is created, the
		// total sits at exactly MAX_SESSIONS_PER_CLIENT.
		$evict_count = ( count( $ours ) - self::MAX_SESSIONS_PER_CLIENT ) + 1;

		for ( $i = 0; $i < $evict_count; $i++ ) {
			$uuid = (string) ( $ours[ $i ]['uuid'] ?? '' );
			if ( '' === $uuid ) {
				continue;
			}

			\WP_Application_Passwords::delete_application_password( $user_id, $uuid );

			McpLogger::log(
				'TOKEN',
				'evicted oldest MCP session to enforce per-client cap',
				[
					'user_id'       => $user_id,
					'client_id'     => $client_id,
					'app_pass_uuid' => $uuid,
				]
			);
		}
	}

	/**
	 * Purge the stored refresh-token rotation marker for a deleted session.
	 *
	 * Hooked on WordPress's `wp_delete_application_password` action so the
	 * per-session user meta is removed wherever the Application Password that
	 * anchors the session is deleted — the revoke endpoint, the admin page, or
	 * WordPress core — leaving no orphaned rows behind.
	 *
	 * @param int                  $user_id WordPress user ID.
	 * @param array<string, mixed> $item    The Application Password record being deleted.
	 * @return void
	 */
	public function purge_refresh_jti_meta( int $user_id, array $item ): void {
		$uuid = (string) ( $item['uuid'] ?? '' );

		if ( '' === $uuid ) {
			return;
		}

		delete_user_meta( $user_id, self::REFRESH_JTI_META_PREFIX . $uuid );
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
