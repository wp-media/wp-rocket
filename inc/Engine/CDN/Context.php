<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;

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
	 * Subscription controller.
	 *
	 * @var SubscriptionController
	 */
	private $subscription_controller;

	/**
	 * Constructor.
	 *
	 * @param Options_Data           $options                 WP Rocket options.
	 * @param SubscriptionController $subscription_controller Subscription controller.
	 */
	public function __construct( Options_Data $options, SubscriptionController $subscription_controller ) {
		$this->options                 = $options;
		$this->subscription_controller = $subscription_controller;
	}

	/**
	 * Gets the currently active CDN driver.
	 *
	 * @return string
	 */
	public function get_driver(): string {
		$cdn_type = $this->get_cdn_type();

		if ( self::ROCKETCDN_TYPE !== $cdn_type ) {
			return self::BYOCDN_TYPE;
		}

		return $this->rocketcdn_resolver();
	}

	/**
	 * Get CDN Type.
	 *
	 * @return string
	 */
	public function get_cdn_type(): string {
		return (string) $this->options->get( 'cdn_type', self::ROCKETCDN_TYPE );
	}

	/**
	 * Is rocketcdn tab is selected.
	 *
	 * @return bool
	 */
	public function is_rocketcdn() {
		return self::ROCKETCDN_TYPE === $this->get_cdn_type();
	}

	/**
	 * Gets the free page limit for the RocketCDN free tier.
	 *
	 * @return int
	 */
	public function get_free_page_limit(): int {
		return 3;
	}

	/**
	 * Resolves RocketCDN to either free or paid type.
	 *
	 * @return string
	 */
	private function rocketcdn_resolver(): string {
		if ( ! $this->subscription_controller->has_active_subscription() && $this->subscription_controller->is_cancelled_outside_grace_period() ) {
			return self::ROCKETCDN_TYPE;
		}

		if ( $this->subscription_controller->is_paid() ) {
			return self::ROCKETCDN_PAID_TYPE;
		}

		return self::ROCKETCDN_FREE_TYPE;
	}
}
