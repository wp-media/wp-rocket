<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN;

use WP_Rocket\Admin\Options;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Engine\Common\Utils;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Mirrors the legacy `cdn` / `cdn_type` fields into `cdn_state` whenever they change.
 */
class CdnStateBridge implements Subscriber_Interface {
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
		];
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
	 * Resolves cdn_state live from the legacy fields, instead of trusting whatever was last
	 * written to the option.
	 *
	 * @param mixed $value   Value returned by an earlier callback on this filter, or null.
	 * @param mixed $default Default value the caller passed to get_rocket_option()/Options_Data::get().
	 *
	 * @return string
	 */
	public function resolve_live( $value, $default ): string {
		return $this->legacy_to_state(
			[
				'cdn'      => get_rocket_option( 'cdn' ),
				'cdn_type' => get_rocket_option( 'cdn_type' ),
			]
		);
	}

	/**
	 * Resolves the cdn_state implied by the legacy `cdn` / `cdn_type` fields and live subscription state.
	 *
	 * @param array $settings Full wp_rocket_settings array (or any array carrying 'cdn' / 'cdn_type').
	 *
	 * @return string One of the Context::CDN_STATE_* / *_TYPE constants.
	 */
	private function legacy_to_state( array $settings ): string {
		if ( empty( $settings['cdn'] ) ) {
			return Context::CDN_STATE_NOTHING;
		}

		$cdn_type = (string) ( $settings['cdn_type'] ?? Context::ROCKETCDN_TYPE );

		if ( Context::ROCKETCDN_TYPE !== $cdn_type ) {
			return Context::BYOCDN_TYPE;
		}

		/**No CDN token means no subscription was ever registered for this install.
		 * The API client returns 'cancelled' as its hardcoded default when there is no token,
		 * which would incorrectly block CDN activation. Skip subscription checks entirely.
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
