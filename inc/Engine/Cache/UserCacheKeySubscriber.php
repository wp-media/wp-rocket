<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Cache;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Buffer\Cache;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Sets and clears the site-specific companion cookie used to confirm the username segment of the
 * `wordpress_logged_in_*` cookie before it is trusted to pick a per-user cache bucket.
 */
class UserCacheKeySubscriber implements Subscriber_Interface {

	/**
	 * WP Rocket options instance.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Constructor.
	 *
	 * @param Options_Data $options WP Rocket options instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_subscribed_events() {
		return [
			'set_logged_in_cookie' => [ 'set_user_cache_cookie', 10, 6 ],
			'clear_auth_cookie'    => 'clear_user_cache_cookie',
			'init'                 => 'maybe_set_user_cache_cookie',
		];
	}

	/**
	 * Sets the companion cookie alongside a genuine WordPress logged-in cookie.
	 *
	 * Only trust this cookie's username if the companion validation cookie matches and has
	 * not expired: the value binds an expiration to the username signature so it cannot be
	 * used past the same expiration as the auth cookie it corroborates.
	 *
	 * @param string $logged_in_cookie The logged-in cookie value.
	 * @param int    $expire           The time the login grace period expires as a UNIX timestamp.
	 * @param int    $expiration       The time the logged-in cookie expires as a UNIX timestamp.
	 * @param int    $user_id          User ID.
	 * @param string $scheme           Authentication scheme. Default 'logged_in'.
	 * @param string $token            User's session token.
	 * @return void
	 */
	public function set_user_cache_cookie( $logged_in_cookie, $expire, $expiration, $user_id, $scheme, $token ) {
		if ( ! $this->options->get( 'cache_logged_user' ) ) {
			return;
		}

		$username_parts = explode( '|', $logged_in_cookie );
		$username       = reset( $username_parts );

		$secret = (string) $this->options->get( 'secret_cache_key' );

		if ( '' === $secret || '' === $username ) {
			return;
		}

		$this->send_user_cache_cookie(
			Cache::get_user_cache_cookie_value( $username, (int) $expiration, $secret ),
			(int) $expire,
			(int) $user_id,
			$secret
		);
	}

	/**
	 * Sets the companion cookie for an authenticated user who does not have a valid one yet.
	 *
	 * Covers sessions where `set_logged_in_cookie` never fired with User Cache enabled: sessions
	 * started before User Cache was enabled or before this cookie existed, and multisite sites the
	 * user did not log in from. Until the cookie is sent back, the page cache is bypassed.
	 *
	 * @return void
	 */
	public function maybe_set_user_cache_cookie() {
		if ( ! $this->options->get( 'cache_logged_user' ) || $this->headers_sent() ) {
			return;
		}

		$secret = (string) $this->options->get( 'secret_cache_key' );

		if ( '' === $secret ) {
			return;
		}

		$auth_cookie = wp_parse_auth_cookie( '', 'logged_in' );

		if ( empty( $auth_cookie['username'] ) || empty( $auth_cookie['expiration'] ) ) {
			return;
		}

		$name    = Cache::get_user_cache_cookie_name( (string) rocket_get_constant( 'COOKIEHASH', '' ), $secret );
		$current = isset( $_COOKIE[ $name ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ $name ] ) ) : '';

		if ( Cache::is_valid_user_cache_cookie_value( $current, $auth_cookie['username'], $secret ) ) {
			return;
		}

		$user_id = get_current_user_id();

		// Only sign a username WordPress itself authenticated from this very logged-in cookie.
		if ( 0 === $user_id || wp_validate_auth_cookie( '', 'logged_in' ) !== $user_id ) {
			return;
		}

		$expiration = (int) $auth_cookie['expiration'];

		$this->send_user_cache_cookie(
			Cache::get_user_cache_cookie_value( $auth_cookie['username'], $expiration, $secret ),
			$expiration,
			$user_id,
			$secret
		);
	}

	/**
	 * Clears the companion cookie alongside the native logged-in cookie on logout.
	 *
	 * @return void
	 */
	public function clear_user_cache_cookie() {
		$secret = (string) $this->options->get( 'secret_cache_key' );

		if ( '' === $secret ) {
			return;
		}

		$name   = Cache::get_user_cache_cookie_name( (string) rocket_get_constant( 'COOKIEHASH', '' ), $secret );
		$expire = time() - (int) rocket_get_constant( 'YEAR_IN_SECONDS', 31536000 );

		$cookie_path      = (string) rocket_get_constant( 'COOKIEPATH', '/' );
		$site_cookie_path = (string) rocket_get_constant( 'SITECOOKIEPATH', '/' );
		$cookie_domain    = (string) rocket_get_constant( 'COOKIE_DOMAIN', '' );

		$this->set_cookie( $name, ' ', $expire, $cookie_path, $cookie_domain, false, true );

		if ( $cookie_path !== $site_cookie_path ) {
			$this->set_cookie( $name, ' ', $expire, $site_cookie_path, $cookie_domain, false, true );
		}
	}

	/**
	 * Sends the companion cookie with the same security flags as the logged-in cookie.
	 *
	 * @param string $value   Cookie value.
	 * @param int    $expire  Cookie expiration as a UNIX timestamp, 0 for a session cookie.
	 * @param int    $user_id User ID.
	 * @param string $secret  Secret cache key of the current site.
	 * @return void
	 */
	private function send_user_cache_cookie( string $value, int $expire, int $user_id, string $secret ): void {
		$secure_logged_in_cookie = is_ssl() && 'https' === wp_parse_url( home_url(), PHP_URL_SCHEME );

		/**
		 * This filter is documented in wp-includes/pluggable.php.
		 */
		$secure_logged_in_cookie = wpm_apply_filters_typed( 'boolean', 'secure_logged_in_cookie', $secure_logged_in_cookie, $user_id, is_ssl() );

		$name = Cache::get_user_cache_cookie_name( (string) rocket_get_constant( 'COOKIEHASH', '' ), $secret );

		$cookie_path      = (string) rocket_get_constant( 'COOKIEPATH', '/' );
		$site_cookie_path = (string) rocket_get_constant( 'SITECOOKIEPATH', '/' );
		$cookie_domain    = (string) rocket_get_constant( 'COOKIE_DOMAIN', '' );

		$this->set_cookie( $name, $value, $expire, $cookie_path, $cookie_domain, $secure_logged_in_cookie, true );

		if ( $cookie_path !== $site_cookie_path ) {
			$this->set_cookie( $name, $value, $expire, $site_cookie_path, $cookie_domain, $secure_logged_in_cookie, true );
		}
	}

	/**
	 * Sets a cookie.
	 *
	 * Isolated in its own method so it can be overridden by a test double instead of the
	 * real `setcookie()`, which is not inspectable under PHPUnit's CLI SAPI.
	 *
	 * @param string $name     Cookie name.
	 * @param string $value    Cookie value.
	 * @param int    $expire   Cookie expiration as a UNIX timestamp.
	 * @param string $path     Cookie path.
	 * @param string $domain   Cookie domain.
	 * @param bool   $secure   Whether the cookie should only be sent over HTTPS.
	 * @param bool   $httponly Whether the cookie is accessible only over HTTP.
	 * @return void
	 */
	protected function set_cookie( string $name, string $value, int $expire, string $path, string $domain, bool $secure, bool $httponly ): void {
		setcookie( $name, $value, $expire, $path, $domain, $secure, $httponly ); // phpcs:ignore WordPress.WP.CookiesInFunctions.CookiesInFunctionsFound
	}

	/**
	 * Checks if HTTP headers have already been sent.
	 *
	 * Isolated in its own method for the same reason as set_cookie(): `headers_sent()` is always
	 * true under PHPUnit's CLI SAPI once output started.
	 *
	 * @return bool
	 */
	protected function headers_sent(): bool {
		return headers_sent();
	}
}
