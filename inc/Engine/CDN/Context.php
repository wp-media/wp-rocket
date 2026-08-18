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
	 * CDN state: no CDN is applied.
	 *
	 * Canonical value shared by APPLIED_STATE_NOTHING and ROCKETCDN_STATE_NOTHING.
	 */
	public const CDN_STATE_NOTHING = 'nothing';

	/**
	 * CDN state: RocketCDN free plan is applied.
	 *
	 * Canonical value shared by ROCKETCDN_FREE_TYPE.
	 */
	public const CDN_STATE_ROCKETCDN_FREE = 'rocketcdn_free';

	/**
	 * CDN state: RocketCDN pro plan is applied.
	 */
	public const CDN_STATE_ROCKETCDN_PRO = 'rocketcdn_pro';

	/**
	 * CDN state: bring-your-own CDN is applied.
	 *
	 * Canonical value shared by BYOCDN_TYPE and APPLIED_STATE_BYOCDN.
	 */
	public const CDN_STATE_BYOCDN = 'byocdn';

	/**
	 * CDN type value for bring-your-own CDN.
	 */
	public const BYOCDN_TYPE = self::CDN_STATE_BYOCDN;

	/**
	 * Resolved RocketCDN type for free users.
	 */
	public const ROCKETCDN_FREE_TYPE = self::CDN_STATE_ROCKETCDN_FREE;

	/**
	 * Resolved RocketCDN type for paid users.
	 */
	public const ROCKETCDN_PAID_TYPE = 'rocketcdn_paid';

	/**
	 * Applied CDN state: no CDN is applied.
	 */
	public const APPLIED_STATE_NOTHING = self::CDN_STATE_NOTHING;

	/**
	 * Applied CDN state: RocketCDN is applied.
	 */
	public const APPLIED_STATE_ROCKETCDN = self::ROCKETCDN_TYPE;

	/**
	 * Applied CDN state: bring-your-own CDN is applied.
	 */
	public const APPLIED_STATE_BYOCDN = self::CDN_STATE_BYOCDN;

	/**
	 * RocketCDN state: no RocketCDN subscription.
	 */
	public const ROCKETCDN_STATE_NOTHING = self::CDN_STATE_NOTHING;

	/**
	 * RocketCDN state: free subscription creation is in progress.
	 */
	public const ROCKETCDN_STATE_ONGOING_FREE = 'ongoing_activation_free';

	/**
	 * RocketCDN state: active free subscription.
	 */
	public const ROCKETCDN_STATE_FREE = 'free';

	/**
	 * RocketCDN state: active paid subscription.
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
	 * Gets the currently applied CDN state.
	 *
	 * @return string One of the CDN_STATE_* constants.
	 */
	public function get_cdn_state(): string {
		$state = (string) $this->options->get( 'cdn_state', self::CDN_STATE_NOTHING );

		$allowed_states = [
			self::CDN_STATE_NOTHING,
			self::CDN_STATE_ROCKETCDN_FREE,
			self::CDN_STATE_ROCKETCDN_PRO,
			self::CDN_STATE_BYOCDN,
		];

		if ( ! in_array( $state, $allowed_states, true ) ) {
			return self::CDN_STATE_NOTHING;
		}

		return $state;
	}

	/**
	 * Gets the currently applied CDN state.
	 *
	 * @return string One of the APPLIED_STATE_* constants.
	 */
	public function get_applied_cdn_state(): string {
		$cdn_state = $this->get_cdn_state();

		if ( self::CDN_STATE_BYOCDN === $cdn_state ) {
			return self::APPLIED_STATE_BYOCDN;
		}

		if ( self::CDN_STATE_ROCKETCDN_FREE === $cdn_state || self::CDN_STATE_ROCKETCDN_PRO === $cdn_state ) {
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

		$cdn_state = $this->get_cdn_state();

		if ( self::CDN_STATE_ROCKETCDN_PRO === $cdn_state ) {
			return self::ROCKETCDN_STATE_PRO;
		}

		if ( self::CDN_STATE_ROCKETCDN_FREE === $cdn_state ) {
			return self::ROCKETCDN_STATE_FREE;
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
