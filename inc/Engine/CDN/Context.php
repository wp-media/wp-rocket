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
	 * Applied CDN state: no CDN is applied.
	 */
	public const APPLIED_STATE_NOTHING = 'nothing';

	/**
	 * Applied CDN state: RocketCDN is applied.
	 */
	public const APPLIED_STATE_ROCKETCDN = 'rocketcdn';

	/**
	 * Applied CDN state: bring-your-own CDN is applied.
	 */
	public const APPLIED_STATE_BYOCDN = 'byocdn';

	/**
	 * RocketCDN state: no RocketCDN subscription.
	 */
	public const ROCKETCDN_STATE_NOTHING = 'nothing';

	/**
	 * RocketCDN state: free subscription creation is in progress.
	 */
	public const ROCKETCDN_STATE_ONGOING_FREE = 'ongoing_activation_free';

	/**
	 * RocketCDN state: active free subscription.
	 */
	public const ROCKETCDN_STATE_FREE = 'free';

	/**
	 * RocketCDN state: active paid subscription, or paid subscription within its grace period.
	 */
	public const ROCKETCDN_STATE_PRO = 'pro';

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
	 * Gets the currently applied CDN state, based on the decoupled CDN toggle flags.
	 *
	 * @return string One of the APPLIED_STATE_* constants.
	 */
	public function get_applied_cdn_state(): string {
		if ( $this->options->get( 'cdn_byocdn_enabled', 0 ) ) {
			return self::APPLIED_STATE_BYOCDN;
		}

		if ( $this->options->get( 'rocketcdn_free_enabled', 0 ) || $this->options->get( 'rocketcdn_pro_enabled', 0 ) ) {
			return self::APPLIED_STATE_ROCKETCDN;
		}

		return self::APPLIED_STATE_NOTHING;
	}

	/**
	 * Gets the current RocketCDN subscription state.
	 *
	 * @return string One of the ROCKETCDN_STATE_* constants.
	 */
	public function get_rocketcdn_state(): string {
		if ( $this->subscription_controller->is_subscription_creation_loading() ) {
			return self::ROCKETCDN_STATE_ONGOING_FREE;
		}

		if ( $this->subscription_controller->is_in_grace_period() ) {
			return self::ROCKETCDN_STATE_PRO;
		}

		if ( $this->subscription_controller->has_active_subscription() ) {
			if ( $this->subscription_controller->is_paid() ) {
				return self::ROCKETCDN_STATE_PRO;
			}

			if ( $this->subscription_controller->is_free() ) {
				return self::ROCKETCDN_STATE_FREE;
			}
		}

		return self::ROCKETCDN_STATE_NOTHING;
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
