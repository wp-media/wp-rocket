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
	private const ROCKETCDN_TYPE = 'rocketcdn';

	/**
	 * CDN type value for bring-your-own CDN.
	 */
	private const BYOCDN_TYPE = 'byocdn';

	/**
	 * Resolved RocketCDN type for free users.
	 */
	private const ROCKETCDN_FREE_TYPE = 'rocketcdn_free';

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
		$cdn_type = (string) $this->options->get( 'cdn_type', self::ROCKETCDN_TYPE );

		if ( self::ROCKETCDN_TYPE !== $cdn_type ) {
			return self::BYOCDN_TYPE;
		}

		return $this->rocketcdn_resolver();
	}

	/**
	 * Resolves RocketCDN to either free or paid type.
	 *
	 * @return string
	 */
	private function rocketcdn_resolver(): string {
		$subscription = $this->api_client->get_subscription_data();

		if ( empty( $subscription['is_active'] ) || 'running' !== $subscription['subscription_status'] ) {
			return self::ROCKETCDN_FREE_TYPE;
		}

		return self::ROCKETCDN_PAID_TYPE;
	}
}
