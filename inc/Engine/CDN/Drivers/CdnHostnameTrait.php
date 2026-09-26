<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN\Drivers;

use WP_Rocket\Engine\CDN\CDN;

/**
 * Shared "does this mode have a usable CDN hostname?" gate, reused by every driver that
 * relies on a configured CDN hostname (Custom, RocketCDNFree, RocketCDNPaid).
 *
 * Provides a memoizing `should_rewrite_url()` that short-circuits to false when no hostname
 * is configured, delegating the remaining, URL-specific logic to `resolve_should_rewrite_url()`.
 */
trait CdnHostnameTrait {
	/**
	 * CDN instance, used to check for configured hostnames.
	 *
	 * @var CDN
	 */
	private $cdn;

	/**
	 * Whether a CDN hostname is configured. URL-independent, memoized once per request.
	 *
	 * @var bool|null
	 */
	private $has_hostname_memo = null;

	/**
	 * Full should_rewrite_url() result per URL. URL-dependent, so each URL is cached
	 * independently rather than relying on a single cached scalar.
	 *
	 * @var array<string, bool>
	 */
	private $should_rewrite_memo = [];

	/**
	 * Should rewrite url or not.
	 *
	 * @param string $url Page Url to check.
	 * @return bool
	 */
	public function should_rewrite_url( string $url ): bool {
		if ( array_key_exists( $url, $this->should_rewrite_memo ) ) {
			return $this->should_rewrite_memo[ $url ];
		}

		if ( ! $this->has_cdn_hostnames() ) {
			$this->should_rewrite_memo[ $url ] = false;

			return false;
		}

		$this->should_rewrite_memo[ $url ] = $this->resolve_should_rewrite_url( $url );

		return $this->should_rewrite_memo[ $url ];
	}

	/**
	 * Checks whether this mode has at least one usable CDN hostname configured.
	 *
	 * @return bool
	 */
	private function has_cdn_hostnames(): bool {
		if ( null === $this->has_hostname_memo ) {
			$this->has_hostname_memo = ! empty( $this->cdn->get_cdn_urls( [ 'all', 'images', 'css_and_js', 'css', 'js' ] ) );
		}

		return $this->has_hostname_memo;
	}

	/**
	 * Resolves the URL-specific "should rewrite" decision, once a usable hostname is confirmed.
	 *
	 * @param string $url Page Url to check.
	 * @return bool
	 */
	abstract protected function resolve_should_rewrite_url( string $url ): bool;
}
