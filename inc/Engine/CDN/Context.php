<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN;

use WP_Rocket\Admin\Options_Data;

/**
 * Handles the CDN driver context.
 */
class Context {
	/**
	 * CDN type value for RocketCDN.
	 */
	public const ROCKETCDN_TYPE = 'rocketcdn';

	/**
	 * CDN type value for bring-your-own CDN.
	 */
	public const BYOCDN_TYPE = 'byocdn';

	/**
	 * Resolved RocketCDN type for free users.
	 */
	public const ROCKETCDN_FREE_TYPE = 'rocketcdn_free';

	/**
	 * Resolved RocketCDN type for paid users.
	 */
	public const ROCKETCDN_PAID_TYPE = 'rocketcdn_paid';

	/**
	 * WP Rocket options.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Constructor.
	 *
	 * @param Options_Data $options WP Rocket options.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * Gets the currently active CDN driver.
	 *
	 * @return string
	 */
	public function get_driver(): string {
		return (string) $this->options->get( 'cdn_type', self::ROCKETCDN_TYPE );
	}

	/**
	 * Gets the free page limit for the RocketCDN free tier.
	 *
	 * @return int
	 */
	public function get_free_page_limit(): int {
		return 3;
	}
}
