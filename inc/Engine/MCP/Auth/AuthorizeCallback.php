<?php
/**
 * Post-login Authorization Callback — Consent Screen.
 *
 * Handles GET /oauth/authorize-callback — called by WordPress after the user
 * authenticates.  Looks up the state transient (which already carries the
 * validated client display data captured at authorize time), refreshes the
 * transient TTL to give the user time to read the screen, then renders a
 * standalone HTML consent page.
 *
 * The user's Allow/Deny choice is handled by ConsentEndpoint (POST /oauth/consent).
 */

declare(strict_types=1);

namespace WP_Rocket\Engine\MCP\Auth;

/**
 * Authorize Callback — renders the consent screen.
 */
class AuthorizeCallback {
	/**
	 * How long (seconds) the state transient lives once the consent screen is shown.
	 * Replaces the original 60 s authorize-window TTL so the user has time to decide.
	 */
	const CONSENT_TTL = 300;

	/**
	 * Handle the post-login callback — show the consent screen.
	 *
	 * @return void
	 */
	public function handle_request(): void {
		$state = sanitize_text_field( wp_unslash( $_GET['state'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- OAuth state token echoed back by the login redirect, not a WP nonce-protected form.

		McpLogger::log(
			'CALLBACK',
			'authorize-callback received',
			[
				'is_logged_in' => is_user_logged_in() ? 'yes' : 'no',
				'user_id'      => is_user_logged_in() ? get_current_user_id() : 0,
				'has_state'    => '' !== $state ? 'yes' : 'no',
				'remote_addr'  => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
			]
		);

		if ( ! is_user_logged_in() ) {
			McpLogger::log( 'CALLBACK', 'rejected: user not logged in' );
			wp_die( esc_html__( 'You must be logged in to authorise an MCP session.', 'rocket' ), esc_html__( 'OAuth Error', 'rocket' ), [ 'response' => 401 ] );
		}

		if ( '' === $state ) {
			McpLogger::log( 'CALLBACK', 'rejected: missing state' );
			wp_die( esc_html__( 'Missing state parameter.', 'rocket' ), esc_html__( 'OAuth Error', 'rocket' ), [ 'response' => 400 ] );
		}

		$state_key  = 'mcp_oauth_state_' . $state;
		$state_data = get_transient( $state_key );

		if ( false === $state_data || ! is_array( $state_data ) ) {
			McpLogger::log( 'CALLBACK', 'rejected: state transient not found or expired', [ 'state' => $state ] );
			wp_die( esc_html__( 'Invalid or expired state. Please restart the authorization flow.', 'rocket' ), esc_html__( 'OAuth Error', 'rocket' ), [ 'response' => 400 ] );
		}

		// Client display data was captured and validated by AuthorizeEndpoint and
		// stored in the state transient. Reusing it avoids a second CIMD lookup —
		// no network round-trip, and no spurious failure if the publisher is
		// briefly unreachable between login and consent.
		$client_id = (string) ( $state_data['client_id'] ?? '' );
		$client    = [
			'client_id'   => $client_id,
			'client_name' => (string) ( $state_data['client_name'] ?? '' ),
			'client_uri'  => (string) ( $state_data['client_uri'] ?? '' ),
			'verified'    => ! empty( $state_data['verified'] ),
			'publisher'   => (string) ( $state_data['publisher'] ?? '' ),
		];

		// Refresh state TTL — the user now has CONSENT_TTL seconds to decide.
		set_transient( $state_key, $state_data, self::CONSENT_TTL );

		McpLogger::log(
			'CALLBACK',
			'showing consent screen',
			[
				'user_id'     => get_current_user_id(),
				'client_id'   => $client_id,
				'client_name' => $client['client_name'],
				'verified'    => $client['verified'] ? 'yes' : 'no',
			]
		);

		$this->render_consent_screen( $state, $client );
	}

	/**
	 * Render a minimal standalone HTML consent page.
	 *
	 * @param string               $state  OAuth state token.
	 * @param array<string, mixed> $client Resolved CIMD client record.
	 * @return void
	 */
	private function render_consent_screen( string $state, array $client ): void {
		nocache_headers();

		// Text values are kept raw here and escaped at each output site below.
		// client_name and publisher come from the fetched CIMD document; keeping
		// their escaping next to the echo/printf that renders them means a later
		// refactor cannot silently drop it. URLs are esc_url'd here because they
		// are only ever emitted into href/action attributes.
		$client_name = (string) ( $client['client_name'] ?? '' );
		$client_id   = esc_url( (string) ( $client['client_id'] ?? '' ) );
		$client_uri  = esc_url( (string) ( $client['client_uri'] ?? '' ) );
		$verified    = ! empty( $client['verified'] );
		$publisher   = (string) ( $client['publisher'] ?? '' );
		$site_name   = (string) get_bloginfo( 'name' );
		$consent_url = esc_url( home_url( '/oauth/consent' ) ); // Rewrite endpoint: home_url(), not get_site_url().

		// The display name links to client_uri if available, otherwise client_id.
		$display_href = '' !== $client_uri ? $client_uri : $client_id;

		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta charset="<?php bloginfo( 'charset' ); ?>">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<title><?php echo esc_html( __( 'Authorize Access', 'rocket' ) . ' — ' . $site_name ); ?></title>
			<style>
				*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
				body {
					font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
					background: #f0f0f1;
					display: flex;
					align-items: center;
					justify-content: center;
					min-height: 100vh;
					padding: 20px;
				}
				.consent-card {
					background: #fff;
					border-radius: 4px;
					box-shadow: 0 1px 3px rgba(0,0,0,.13);
					max-width: 420px;
					width: 100%;
					padding: 36px 40px 40px;
				}
				.consent-card h1 {
					font-size: 1.1rem;
					font-weight: 600;
					color: #1d2327;
					margin-bottom: 20px;
					text-align: center;
				}
				.client-block {
					border: 1px solid #ddd;
					border-radius: 3px;
					padding: 14px 16px;
					margin-bottom: 20px;
				}
				.client-name {
					font-size: 1rem;
					font-weight: 600;
					color: #1d2327;
				}
				.client-name a { color: inherit; text-decoration: none; }
				.client-name a:hover { text-decoration: underline; }
				.client-url {
					font-size: .78rem;
					color: #646970;
					word-break: break-all;
					margin-top: 4px;
				}
				.client-url a { color: #646970; }
				.verified-badge {
					display: inline-block;
					font-size: .72rem;
					font-weight: 600;
					color: #00a32a;
					background: #edfaef;
					border: 1px solid #a7e8b1;
					border-radius: 2px;
					padding: 2px 6px;
					margin-top: 8px;
				}
				.scope-text {
					font-size: .875rem;
					color: #3c434a;
					margin-bottom: 24px;
					line-height: 1.5;
				}
				.consent-actions {
					display: flex;
					gap: 10px;
				}
				.btn {
					flex: 1;
					padding: 9px 14px;
					font-size: .875rem;
					font-weight: 600;
					border-radius: 3px;
					border: 1px solid transparent;
					cursor: pointer;
					text-align: center;
				}
				.btn-allow {
					background: #2271b1;
					color: #fff;
					border-color: #2271b1;
				}
				.btn-allow:hover { background: #135e96; border-color: #135e96; }
				.btn-deny {
					background: #fff;
					color: #d63638;
					border-color: #d63638;
				}
				.btn-deny:hover { background: #fcf0f1; }
			</style>
		</head>
		<body>
			<div class="consent-card">
				<h1><?php esc_html_e( 'Authorize access to your site?', 'rocket' ); ?></h1>

				<div class="client-block">
					<div class="client-name">
						<?php if ( '' !== $display_href ) : ?>
							<a href="<?php echo esc_url( $display_href ); ?>" rel="noopener noreferrer" target="_blank"><?php echo esc_html( $client_name ); ?></a>
						<?php else : ?>
							<?php echo esc_html( $client_name ); ?>
						<?php endif; ?>
					</div>
					<?php if ( '' !== $client_id ) : ?>
						<div class="client-url">
							<?php esc_html_e( 'ID:', 'rocket' ); ?>
							<a href="<?php echo esc_url( $client_id ); ?>" rel="noopener noreferrer" target="_blank"><?php echo esc_html( $client_id ); ?></a>
						</div>
					<?php endif; ?>
					<?php if ( $verified && '' !== $publisher ) : ?>
						<div class="verified-badge">
							<?php
							/* translators: %s: publisher name */
							printf( esc_html__( 'Verified publisher: %s', 'rocket' ), esc_html( $publisher ) );
							?>
						</div>
					<?php endif; ?>
				</div>

				<p class="scope-text">
					<?php
					printf(
						/* translators: 1: client name, 2: site name */
						esc_html__( '%1$s is requesting access to the WP Rocket MCP tools on %2$s on your behalf.', 'rocket' ),
						'<strong>' . esc_html( $client_name ) . '</strong>',
						'<strong>' . esc_html( $site_name ) . '</strong>'
					);
					?>
				</p>

				<form method="post" action="<?php echo esc_url( $consent_url ); ?>">
					<input type="hidden" name="state" value="<?php echo esc_attr( $state ); ?>">
					<?php wp_nonce_field( 'mcp_consent_' . $state, 'mcp_consent_nonce' ); ?>
					<div class="consent-actions">
						<button type="submit" name="mcp_action" value="allow" class="btn btn-allow">
							<?php esc_html_e( 'Allow', 'rocket' ); ?>
						</button>
						<button type="submit" name="mcp_action" value="deny" class="btn btn-deny">
							<?php esc_html_e( 'Deny', 'rocket' ); ?>
						</button>
					</div>
				</form>
			</div>
		</body>
		</html>
		<?php
		exit;
	}
}
