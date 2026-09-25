<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN\Drivers;

use WP_Rocket\Engine\CDN\CDN;
use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Engine\CDN\RocketCDN\Database\Queries\RocketCDN;

class RocketCDNFree implements DriverInterface {
	use CdnHostnameTrait;

	/**
	 * Query instance.
	 *
	 * @var RocketCDN
	 */
	private $query;

	/**
	 * CDN Context, used for the forced-off guard.
	 *
	 * @var Context
	 */
	private $context;

	/**
	 * Constructor.
	 *
	 * @param CDN       $cdn     CDN instance.
	 * @param RocketCDN $query   Query instance.
	 * @param Context   $context CDN Context instance.
	 */
	public function __construct( CDN $cdn, RocketCDN $query, Context $context ) {
		$this->cdn     = $cdn;
		$this->query   = $query;
		$this->context = $context;
	}

	/**
	 * Should rewrite url or not, once a usable CDN hostname is confirmed.
	 *
	 * @param string $url Page Url to check.
	 * @return bool
	 */
	protected function resolve_should_rewrite_url( string $url ): bool {
		if ( $this->context->is_forced_off() ) {
			return false;
		}

		return $this->query->is_url_found( $url );
	}
}
