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
	 * Guards against flushing the subscription cache more than once per REST request.
	 *
	 * @var bool
	 */
	private bool $subscription_flushed = false;

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
			'pre_get_rocket_option_cdn'        => [ 'resolve_live_cdn', 10, 2 ],
			'wp_rocket_upgrade'                => 'backfill_cdn_state_on_upgrade',
		];
	}

	/**
	 * Backfills cdn_state for sites upgrading from a version where the key never existed.
	 *
	 * Utils::did_setting_change() requires the key to already exist in the old value to
	 * report a change, so on any site where cdn_state has never been written, the first
	 * real cdn/cdn_type transition after this ships would silently fail to trigger
	 * Subscriber::maybe_clear_cache(). Writing the key here first - reflecting whatever
	 * state is already live - means that first real transition afterward compares against
	 * a properly-populated old value. This write itself doesn't touch cdn/cdn_type, so it
	 * doesn't trigger reconcile() or a cache clear - nothing about the site's active CDN
	 * behavior actually changed, only the tracking field catching up to it.
	 *
	 * @return void
	 */
	public function backfill_cdn_state_on_upgrade(): void {
		$settings = $this->options_api->get( 'settings', [] );

		if ( isset( $settings['cdn_state'] ) ) {
			return;
		}

		$settings['cdn_state'] = $this->resolve_live( null, Context::CDN_STATE_NOTHING );

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
	 * Resolves cdn_state live from the legacy fields, instead of trusting whatever was last
	 * written to the option.
	 *
	 * In REST context (React CDN CTA loading), flushes the subscription cache once per
	 * request so that a stale rocketcdn_status transient — e.g. from before the user
	 * upgraded their plan externally on rocketcdn.me — does not cause is_paid() to
	 * return the wrong result. The flush triggers a fresh API call; the response is
	 * re-cached for one day, so subsequent page loads within that window are cheap.
	 *
	 * Reads cdn/cdn_type from the raw options store to bypass get_rocket_option() and the
	 * apply_pause_on_rocketcdn_only filter, which returns 1 for byocdn users when
	 * is_admin() is false (e.g. REST context), making CDN appear active when it is not.
	 *
	 * @param mixed $value   Value returned by an earlier callback on this filter, or null.
	 * @param mixed $default Default value the caller passed to get_rocket_option()/Options_Data::get().
	 *
	 * @return string
	 */
	public function resolve_live( $value, $default ): string {
		if ( ! $this->subscription_flushed && rocket_get_constant( 'REST_REQUEST', false ) ) {
			$this->subscription_controller->reset_subscription_data();
			$this->subscription_flushed = true;
		}

		$settings = $this->options_api->get( 'settings', [] );

		return $this->legacy_to_state(
			[
				'cdn'      => $settings['cdn'] ?? 0,
				'cdn_type' => $settings['cdn_type'] ?? Context::ROCKETCDN_TYPE,
			]
		);
	}

	/**
	 * Resolves 'cdn' live from $this->options_api, instead of trusting whatever
	 * Options_Data snapshot the caller's instance happens to hold.
	 *
	 * The 'options' container service is registered with add(), not addShared() (see
	 * class-options.php), so every class gets its own independently-resolved Options_Data
	 * instance, frozen with whatever 'settings' looked like when that instance was built.
	 * A write made through one instance (e.g. CDNOptionsManager::enable()/disable()) is
	 * therefore never visible to another class's instance for the rest of the request -
	 * there is no single shared object a write could propagate through. This filter makes
	 * every 'cdn' read live instead, the same way pre_get_rocket_option_cdn_state already
	 * does for cdn_state.
	 *
	 * Always returning a non-null value here short-circuits Options_Data::get() before it
	 * ever reaches its own get_rocket_option_cdn post-filter application - which would
	 * silently stop Subscriber::apply_pause_on_rocketcdn_only() (forces 'cdn' on for a
	 * BYOCDN driver on the front end) from ever running. Re-apply that same post-filter
	 * here so it still fires against the live value instead of being bypassed.
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

		if ( $this->subscription_controller->is_cancelled_outside_grace_period() ) {
			return Context::CDN_STATE_NOTHING;
		}

		if ( $this->subscription_controller->is_paid() ) {
			return Context::ROCKETCDN_PAID_TYPE;
		}

		return Context::ROCKETCDN_FREE_TYPE;
	}
}
