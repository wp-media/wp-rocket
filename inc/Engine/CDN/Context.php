<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Engine\License\API\User;

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
	 */
	public const CDN_STATE_NOTHING = 'nothing';

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
	 * RocketCDN state: free subscription creation is in progress.
	 *
	 * The only state that isn't a CDN_STATE_* value — it's a live transient,
	 * never persisted in the cdn_state option.
	 */
	public const ROCKETCDN_STATE_ONGOING_FREE = 'ongoing_activation_free';

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
	 * License User instance.
	 *
	 * @var User
	 */
	private $user;

	/**
	 * Which condition matched on the most recent {@see is_forced_off()} call.
	 *
	 * @var string|null One of 'pro_cancelled_in_grace_period', 'pro_cancelled_outside_grace', 'license_expired',
	 *                   'license_banned', or null.
	 */
	private $forced_off_reason;

	/**
	 * Constructor.
	 *
	 * @param Options_Data           $options                 WP Rocket options.
	 * @param SubscriptionController $subscription_controller Subscription controller.
	 * @param User                   $user                    License User instance.
	 */
	public function __construct( Options_Data $options, SubscriptionController $subscription_controller, User $user ) {
		$this->options                 = $options;
		$this->subscription_controller = $subscription_controller;
		$this->user                    = $user;
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
	 * @param string|null $cdn_state Optional.
	 *
	 * @return string One of CDN_STATE_NOTHING, ROCKETCDN_FREE_TYPE, ROCKETCDN_PAID_TYPE or BYOCDN_TYPE.
	 */
	public function get_cdn_state( ?string $cdn_state = null ): string {
		$state = $cdn_state ?? (string) $this->options->get( 'cdn_state', self::CDN_STATE_NOTHING );

		$allowed_states = [
			self::CDN_STATE_NOTHING,
			self::ROCKETCDN_FREE_TYPE,
			self::ROCKETCDN_PAID_TYPE,
			self::BYOCDN_TYPE,
		];

		if ( ! in_array( $state, $allowed_states, true ) ) {
			return self::CDN_STATE_NOTHING;
		}

		return $state;
	}

	/**
	 * Gets the CDN driver type implied by the currently applied CDN state.
	 *
	 * @param string|null $cdn_state Optional.
	 *
	 * @return string One of CDN_STATE_NOTHING, ROCKETCDN_TYPE or BYOCDN_TYPE.
	 */
	public function get_applied_cdn_state( ?string $cdn_state = null ): string {
		$cdn_state = $this->get_cdn_state( $cdn_state );

		if ( self::BYOCDN_TYPE === $cdn_state ) {
			return self::BYOCDN_TYPE;
		}

		if ( self::ROCKETCDN_FREE_TYPE === $cdn_state || self::ROCKETCDN_PAID_TYPE === $cdn_state ) {
			return self::ROCKETCDN_TYPE;
		}

		return self::CDN_STATE_NOTHING;
	}

	/**
	 * Gets the current RocketCDN subscription state.
	 *
	 * @param string|null $cdn_state CDN state.
	 *
	 * @return string One of the ROCKETCDN_STATE_* constants.
	 */
	public function get_rocketcdn_state( ?string $cdn_state = null ): string {
		if ( $this->subscription_controller->is_subscription_creation_loading() ) {
			return self::ROCKETCDN_STATE_ONGOING_FREE;
		}

		$cdn_state = $this->get_cdn_state( $cdn_state );

		if ( self::ROCKETCDN_PAID_TYPE === $cdn_state ) {
			return self::ROCKETCDN_PAID_TYPE;
		}

		if ( self::ROCKETCDN_FREE_TYPE === $cdn_state ) {
			return self::ROCKETCDN_FREE_TYPE;
		}

		return self::CDN_STATE_NOTHING;
	}

	/**
	 * Gets the canonical `cdn_status` tracking axis value.
	 *
	 * @param string|null $cdn_state Optional. Overrides the persisted `cdn_state`, for a caller
	 *                               that just wrote a new mode and needs the status computed
	 *                               against it rather than the stale, per-request options snapshot.
	 *
	 * @return string One of 'active', 'inactive', 'forced_off' or 'ongoing_activation'.
	 */
	public function get_cdn_status( ?string $cdn_state = null ): string {
		switch ( true ) {
			case $this->is_forced_off():
				return 'forced_off';

			case $this->subscription_controller->is_subscription_creation_loading():
				return 'ongoing_activation';

			case self::CDN_STATE_NOTHING !== $this->get_cdn_state( $cdn_state ):
				return 'active';

			default:
				return 'inactive';
		}
	}

	/**
	 * Determines whether the CDN should be force-paused due to an inactive or invalid subscription state.
	 *
	 * @since 3.22
	 *
	 * @return bool True if the CDN should be force-paused, false otherwise.
	 */
	public function is_forced_off(): bool {
		$this->forced_off_reason = null;

		// Force paused if paid plan cancelled but in grace period.
		if ( $this->subscription_controller->is_paid() && $this->subscription_controller->is_in_grace_period() ) {
			$this->forced_off_reason = 'pro_cancelled_in_grace_period';

			return true;
		}

		if ( $this->subscription_controller->is_paid() && $this->subscription_controller->is_cancelled_outside_grace_period() ) {
			$this->forced_off_reason = 'pro_cancelled_outside_grace';

			return true;
		}

		// Force paused if free plan with an invalid WP Rocket licence.
		if ( $this->subscription_controller->is_free() && $this->subscription_controller->is_license_invalid() ) {

			$this->forced_off_reason = 'license_expired';

			// Distinquish between a revoked licence and an expired licence, so the correct reason can be tracked.
			if ( $this->user->is_revoked() ) {
				$this->forced_off_reason = 'license_banned';
			}

			return true;
		}

		// Force paused if subscription cancelled beyond the grace period and WP Rocket licence is invalid.
		if ( $this->subscription_controller->is_cancelled_outside_grace_period() && $this->subscription_controller->is_license_invalid() ) {
			$this->forced_off_reason = 'pro_cancelled_outside_grace';

			return true;
		}

		return false;
	}

	/**
	 * Gets which condition matched on the most recent {@see is_forced_off()} call.
	 *
	 * @return string|null One of 'pro_cancelled_in_grace_period', 'pro_cancelled_outside_grace', 'license_expired',
	 *                      'license_banned', or null when RocketCDN isn't (or wasn't last checked as) forced off.
	 */
	public function get_forced_off_reason(): ?string {
		return $this->forced_off_reason;
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
