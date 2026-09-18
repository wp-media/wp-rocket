<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Cache;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Sets and clears the companion cookie used to confirm the username segment of the
 * `wordpress_logged_in_*` cookie before it is trusted to pick a per-user cache bucket.
 */
class UserCacheKeySubscriber implements Subscriber_Interface {

	/**
	 * Prefix used to build the companion cookie name.
	 *
	 * Must be kept in sync with the literal prefix used in
	 * WP_Rocket\Buffer\Cache::is_valid_user_cache_cookie() — that class runs before WordPress
	 * (and therefore this class) is loaded, so the two cannot share a constant.
	 *
	 * @var string
	 */
	const COOKIE_PREFIX = 'wp_rocket_ucc_';

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

		$mac   = hash_hmac( 'sha256', $username . '|' . $expiration, $secret );
		$value = $expiration . '|' . $mac;

		$secure_logged_in_cookie = is_ssl() && 'https' === wp_parse_url( home_url(), PHP_URL_SCHEME );

		/**
		 * This filter is documented in wp-includes/pluggable.php.
		 */
		$secure_logged_in_cookie = wpm_apply_filters_typed( 'boolean', 'secure_logged_in_cookie', $secure_logged_in_cookie, $user_id, is_ssl() );

		$name = self::COOKIE_PREFIX . COOKIEHASH;

		$this->set_cookie( $name, $value, (int) $expire, COOKIEPATH, COOKIE_DOMAIN, $secure_logged_in_cookie, true );

		if ( COOKIEPATH !== SITECOOKIEPATH ) {
			$this->set_cookie( $name, $value, (int) $expire, SITECOOKIEPATH, COOKIE_DOMAIN, $secure_logged_in_cookie, true );
		}
	}

	/**
	 * Clears the companion cookie alongside the native logged-in cookie on logout.
	 *
	 * @return void
	 */
	public function clear_user_cache_cookie() {
		$name   = self::COOKIE_PREFIX . COOKIEHASH;
		$expire = time() - YEAR_IN_SECONDS;

		$this->set_cookie( $name, ' ', $expire, COOKIEPATH, COOKIE_DOMAIN, false, true );

		if ( COOKIEPATH !== SITECOOKIEPATH ) {
			$this->set_cookie( $name, ' ', $expire, SITECOOKIEPATH, COOKIE_DOMAIN, false, true );
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
}
