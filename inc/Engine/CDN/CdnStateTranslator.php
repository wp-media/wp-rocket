<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN;

use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;

/**
 * Pure translation between the legacy `cdn` / `cdn_type` fields and the `cdn_state` option.
 *
 * Both directions must stay single-sourced from here: Story 5's plugin-update migration and
 * CdnStateBridge both need to agree on what a given legacy shape means, and what a given
 * cdn_state value looks like as legacy fields. Neither method writes anything - callers persist.
 */
class CdnStateTranslator {
	/**
	 * Subscription controller, used to resolve RocketCDN free vs. paid and cancellation state.
	 *
	 * @var SubscriptionController
	 */
	private $subscription_controller;

	/**
	 * Constructor.
	 *
	 * @param SubscriptionController $subscription_controller Subscription controller.
	 */
	public function __construct( SubscriptionController $subscription_controller ) {
		$this->subscription_controller = $subscription_controller;
	}

	/**
	 * Resolves the cdn_state implied by the legacy `cdn` / `cdn_type` fields and live subscription state.
	 *
	 * Mirrors Context::get_driver()'s logic, with the `cdn` on/off flag folded in - a disabled
	 * CDN or a subscription that's fully cancelled outside its grace period both resolve to
	 * CDN_STATE_NOTHING, matching what DriverFactory would actually apply on the frontend today.
	 *
	 * @param array $settings Full wp_rocket_settings array (or any array carrying 'cdn' / 'cdn_type').
	 *
	 * @return string One of the Context::CDN_STATE_* / *_TYPE constants.
	 */
	public function legacy_to_state( array $settings ): string {
		if ( empty( $settings['cdn'] ) ) {
			return Context::CDN_STATE_NOTHING;
		}

		$cdn_type = (string) ( $settings['cdn_type'] ?? Context::ROCKETCDN_TYPE );

		if ( Context::ROCKETCDN_TYPE !== $cdn_type ) {
			return Context::BYOCDN_TYPE;
		}

		if (
			! $this->subscription_controller->has_active_subscription()
			&&
			$this->subscription_controller->is_cancelled_outside_grace_period()
		) {
			return Context::CDN_STATE_NOTHING;
		}

		if ( $this->subscription_controller->is_paid() ) {
			return Context::ROCKETCDN_PAID_TYPE;
		}

		return Context::ROCKETCDN_FREE_TYPE;
	}

	/**
	 * Resolves the legacy `cdn` / `cdn_type` shape implied by a cdn_state value.
	 *
	 * Free vs. paid isn't distinguished on the legacy side - both map to the `rocketcdn` driver
	 * type, exactly as Context::get_driver() already resolves free/paid live from the
	 * subscription controller rather than from a stored field.
	 *
	 * @param string $state One of the Context::CDN_STATE_* / *_TYPE constants.
	 *
	 * @return array{cdn: int, cdn_type: string}
	 */
	public function state_to_legacy( string $state ): array {
		if ( Context::BYOCDN_TYPE === $state ) {
			return [
				'cdn'      => 1,
				'cdn_type' => Context::BYOCDN_TYPE,
			];
		}

		if ( Context::ROCKETCDN_FREE_TYPE === $state || Context::ROCKETCDN_PAID_TYPE === $state ) {
			return [
				'cdn'      => 1,
				'cdn_type' => Context::ROCKETCDN_TYPE,
			];
		}

		// CDN_STATE_NOTHING, or anything unrecognized - fail closed, same as Context::get_cdn_state().
		return [
			'cdn' => 0,
		];
	}
}
