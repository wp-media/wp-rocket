<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Resolves `cdn_state` live from the legacy `cdn` / `cdn_type` fields, instead of trusting
 * whatever was last written to the option.
 *
 * `Options_Data::get()` applies a `pre_get_rocket_option_{$key}` filter to every read before it
 * ever looks at storage, regardless of which class or helper made the call. Registering on
 * `pre_get_rocket_option_cdn_state` means every existing consumer - Context::get_cdn_state(),
 * the get-options ability, get_rocket_option( 'cdn_state' ) from anywhere else - gets the live
 * value for free, with no call-site changes.
 *
 * This closes a hole CdnStateBridge cannot close by itself: `cdn` / `cdn_type` can be forced at
 * read time by a filter that never writes to the option (Render\Controller's own
 * maybe_pause_cdn_for_inactive_subscription(), hooked on `pre_get_rocket_option_cdn`, is a
 * first-party example; a third party using the same extension point is another). A write-time
 * sync can never observe a read-time override with no associated write - so cdn_state would
 * otherwise disagree with what Context::get_driver() actually applies for as long as such a
 * filter is active. Recomputing on every read means there is no persisted value to go stale in
 * the first place.
 *
 * Temporary scaffolding for the RocketCDN refactor epic (#8693), same removal criterion as
 * CdnStateBridge: delete once Story 10 lands and every mode writes/reads cdn_state directly.
 */
class CdnStateResolver implements Subscriber_Interface {
	/**
	 * Translator between the legacy fields and cdn_state.
	 *
	 * @var CdnStateTranslator
	 */
	private $translator;

	/**
	 * Constructor.
	 *
	 * @param CdnStateTranslator $translator Translator between the legacy fields and cdn_state.
	 */
	public function __construct( CdnStateTranslator $translator ) {
		$this->translator = $translator;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_subscribed_events(): array {
		return [
			'pre_get_rocket_option_cdn_state' => [ 'resolve', 10, 2 ],
		];
	}

	/**
	 * Computes cdn_state from the legacy fields, read through get_rocket_option() so any filter
	 * forcing them (forced-pause, third-party overrides) is already applied.
	 *
	 * @param mixed $value   Value returned by an earlier callback on this filter, or null.
	 * @param mixed $default Default value the caller passed to get_rocket_option()/Options_Data::get().
	 *
	 * @return string
	 */
	public function resolve( $value, $default ): string {
		return $this->translator->legacy_to_state(
			[
				'cdn'      => get_rocket_option( 'cdn' ),
				'cdn_type' => get_rocket_option( 'cdn_type' ),
			]
		);
	}
}
