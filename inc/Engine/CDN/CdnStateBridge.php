<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN;

use WP_Rocket\Admin\Options;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Engine\Common\Utils;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Mirrors the legacy `cdn` / `cdn_type` fields into `cdn_state` whenever they change, and
 * resolves `cdn` itself live (see resolve_live_cdn()).
 */
class CdnStateBridge implements Subscriber_Interface {
	/**
	 * Priority at which {@see resolve_live_cdn()} seeds `pre_get_rocket_option_cdn`, ahead of
	 * callbacks at the default priority 10 that expect to observe or override its result.
	 *
	 * @var int
	 */
	private const CDN_SEEDER_PRIORITY = 5;

	/**
	 * Subscription controller, used to resolve RocketCDN free vs. paid and cancellation state.
	 *
	 * @var SubscriptionController
	 */
	private $subscription_controller;

	/**
	 * WP Options API instance.
	 *
	 * @var Options
	 */
	private $options_api;

	/**
	 * Constructor.
	 *
	 * @param SubscriptionController $subscription_controller Subscription controller.
	 * @param Options                $options_api              WP Options API instance.
	 */
	public function __construct( SubscriptionController $subscription_controller, Options $options_api ) {
		$this->subscription_controller = $subscription_controller;
		$this->options_api             = $options_api;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_subscribed_events(): array {
		return [
			'update_option_wp_rocket_settings' => [ 'reconcile', 5, 2 ],
			'pre_get_rocket_option_cdn_state'  => [ 'resolve_live', 10, 2 ],
			'pre_get_rocket_option_cdn'        => [ 'resolve_live_cdn', self::CDN_SEEDER_PRIORITY, 2 ],
			'wp_rocket_upgrade'                => [ 'backfill_cdn_state_on_upgrade', 12 ],
		];
	}

	/**
	 * Backfills cdn_state for sites upgrading from a version where the key never existed, so
	 * Utils::did_setting_change() has a populated old value to compare the first real transition against.
	 *
	 * @return void
	 */
	public function backfill_cdn_state_on_upgrade(): void {
		$settings = $this->options_api->get( 'settings', [] );

		if ( isset( $settings['cdn_state'] ) ) {
			return;
		}

		// Uses legacy_to_state() directly, not resolve_live(): this writes the backfilled
		// value to the DB, and resolve_live()'s null return means "don't override" - the
		// right semantics for a read-time filter, but not a value that belongs in the option.
		$settings['cdn_state'] = $this->legacy_to_state(
			[
				'cdn'      => get_rocket_option( 'cdn' ),
				'cdn_type' => get_rocket_option( 'cdn_type' ),
			]
		);

		$this->options_api->set( 'settings', $settings );
	}

	/**
	 * Recomputes cdn_state from the legacy fields after a settings save, if either changed.
	 *
	 * @param mixed $old_value Previous wp_rocket_settings value.
	 * @param mixed $value     New wp_rocket_settings value.
	 *
	 * @return void
	 */
	public function reconcile( $old_value, $value ): void {
		if ( ! is_array( $old_value ) || ! is_array( $value ) ) {
			return;
		}

		if (
			! Utils::did_setting_change( 'cdn', $old_value, $value )
			&&
			! Utils::did_setting_change( 'cdn_type', $old_value, $value )
		) {
			return;
		}

		$new_state = $this->legacy_to_state( $value );

		if ( ( $value['cdn_state'] ?? null ) === $new_state ) {
			return;
		}

		$value['cdn_state'] = $new_state;

		$this->options_api->set( 'settings', $value );
	}

	/**
	 * Resolves cdn_state live from raw cdn/cdn_type. Returns null with no prior subscription;
	 * also live-forces 'nothing' during a grace period or invalid free licence, never persisted.
	 *
	 * @param mixed $value   Value returned by an earlier callback on this filter, or null.
	 * @param mixed $default Default value the caller passed to get_rocket_option()/Options_Data::get().
	 *
	 * @return string|null
	 */
	public function resolve_live( $value, $default ): ?string {
		if ( $this->subscription_controller->is_cancelled_outside_grace_period() ) {
			return null;
		}

		$settings = $this->options_api->get( 'settings', [] );
		$cdn_type = (string) ( $settings['cdn_type'] ?? Context::ROCKETCDN_TYPE );

		if ( Context::ROCKETCDN_TYPE === $cdn_type ) {
			if ( $this->subscription_controller->is_paid() && $this->subscription_controller->is_in_grace_period() ) {
				return Context::CDN_STATE_NOTHING;
			}

			if ( $this->subscription_controller->is_free() && $this->subscription_controller->is_license_invalid() ) {
				return Context::CDN_STATE_NOTHING;
			}
		}

		return $this->legacy_to_state(
			[
				'cdn'      => $settings['cdn'] ?? 0,
				'cdn_type' => $cdn_type,
			]
		);
	}

	/**
	 * Resolves 'cdn' live from $this->options_api instead of a per-instance stale Options_Data
	 * snapshot, re-applying the get_rocket_option_cdn post-filter for later-priority overriders.
	 *
	 * @param mixed $value   Value returned by an earlier callback on this filter, or null.
	 * @param mixed $default Default value the caller passed to get_rocket_option()/Options_Data::get().
	 *
	 * @return mixed
	 */
	public function resolve_live_cdn( $value, $default ) {
		$settings = $this->options_api->get( 'settings', [] );
		$live     = $settings['cdn'] ?? $default;

		return wpm_apply_filters_typed( 'boolean|integer|string', 'get_rocket_option_cdn', $live, $default );
	}

	/**
	 * Resolves the cdn_state implied by the legacy `cdn` / `cdn_type` fields and live subscription state.
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

		/**
		 * No token means no subscription was ever registered; skip subscription checks
		 * entirely, since the API client's hardcoded 'cancelled' default would otherwise block activation.
		 */
		if ( ! $this->subscription_controller->has_token() ) {
			return Context::ROCKETCDN_FREE_TYPE;
		}

		if ( $this->subscription_controller->is_cancelled_outside_grace_period() ) {
			return Context::CDN_STATE_NOTHING;
		}

		if ( $this->subscription_controller->is_paid() ) {
			return Context::ROCKETCDN_PAID_TYPE;
		}

		return Context::ROCKETCDN_FREE_TYPE;
	}
}
