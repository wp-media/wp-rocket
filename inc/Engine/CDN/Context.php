<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\RocketCDN\APIClient;

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
	private const ROCKETCDN_PAID_TYPE = 'rocketcdn_paid';

	/**
	 * WP Rocket options.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * RocketCDN API client.
	 *
	 * @var APIClient
	 */
	private $api_client;

	/**
	 * Constructor.
	 *
	 * @param Options_Data $options    WP Rocket options.
	 * @param APIClient    $api_client RocketCDN API client.
	 */
	public function __construct( Options_Data $options, APIClient $api_client ) {
		$this->options    = $options;
		$this->api_client = $api_client;
	}

	/**
	 * Gets the currently active CDN driver.
	 *
	 * @return string
	 */
	public function get_driver(): string {
		return (string) $this->options->get( 'cdn_type', self::ROCKETCDN_TYPE );
	}
}
