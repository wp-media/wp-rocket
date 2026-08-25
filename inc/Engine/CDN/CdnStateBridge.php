<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN;

use WP_Rocket\Admin\Options;
use WP_Rocket\Engine\Common\Utils;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Corrects the legacy `cdn` / `cdn_type` fields whenever something writes `cdn_state` directly,
 * so code that hasn't migrated to reading cdn_state yet keeps working.
 *
 * This is one half of the compatibility bridge for the RocketCDN refactor epic (#8693) - the
 * other half, legacy fields -> cdn_state, is CdnStateResolver, which resolves that direction
 * live on every read instead of caching it here. This class only ever needs to run the opposite
 * direction: cdn_state is genuinely persisted data going forward, so when something writes it
 * directly (Story 2's toggle UI, the wp-rocket/set-option ability), the legacy fields - real
 * stored data other, unmigrated code still reads directly - need to be corrected to match.
 *
 * Its removal criterion is Story 10 - once nothing reads the legacy fields directly any more,
 * this class and CdnStateTranslator can both be deleted along with them.
 *
 * It does no more than reconcile option shapes: no cache clearing, no API calls. Those stay the
 * sole responsibility of whatever already reacts to `update_option_wp_rocket_settings` at a
 * later priority (e.g. Subscriber::maybe_clear_cache()), which this class runs ahead of so it
 * always sees settled legacy fields.
 */
class CdnStateBridge implements Subscriber_Interface {
	/**
	 * Runs ahead of the cache-clearing subscriber on the same hook, so it always sees a
	 * reconciled cdn_state rather than a half-written one.
	 */
	const PRIORITY = 5;

	/**
	 * Hard backstop against runaway recursion. The reconciliation is idempotent by
	 * construction - a settled state should never trigger a further write - but a static
	 * depth guard makes "cannot recurse" true regardless of whether that holds in every case.
	 */
	const MAX_DEPTH = 1;

	/**
	 * Current re-entrancy depth.
	 *
	 * @var int
	 */
	private static $depth = 0;

	/**
	 * Translator between the legacy fields and cdn_state.
	 *
	 * @var CdnStateTranslator
	 */
	private $translator;

	/**
	 * WP Options API instance.
	 *
	 * @var Options
	 */
	private $options_api;

	/**
	 * Constructor.
	 *
	 * @param CdnStateTranslator $translator  Translator between the legacy fields and cdn_state.
	 * @param Options            $options_api WP Options API instance.
	 */
	public function __construct( CdnStateTranslator $translator, Options $options_api ) {
		$this->translator  = $translator;
		$this->options_api = $options_api;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_subscribed_events(): array {
		return [
			'update_option_wp_rocket_settings' => [ 'reconcile', self::PRIORITY, 2 ],
		];
	}

	/**
	 * Reconciles the legacy fields against cdn_state after a settings save.
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

		if ( self::$depth >= self::MAX_DEPTH ) {
			return;
		}

		if ( Utils::did_setting_change( 'cdn_state', $old_value, $value ) ) {
			$this->sync_state_to_legacy( $value );
		}
	}

	/**
	 * Corrects the legacy fields to match the cdn_state that just changed.
	 *
	 * @param array $value New wp_rocket_settings value.
	 *
	 * @return void
	 */
	private function sync_state_to_legacy( array $value ): void {
		$state           = (string) ( $value['cdn_state'] ?? Context::CDN_STATE_NOTHING );
		$expected_legacy = $this->translator->state_to_legacy( $state );

		$needs_write = false;

		foreach ( $expected_legacy as $key => $expected ) {
			if ( ( $value[ $key ] ?? null ) !== $expected ) {
				$needs_write = true;
				break;
			}
		}

		if ( ! $needs_write ) {
			return;
		}

		$this->persist( array_merge( $value, $expected_legacy ) );
	}

	/**
	 * Persists a corrected settings array, guarded against re-entrant reconciliation.
	 *
	 * @param array $settings Full settings array to persist.
	 *
	 * @return void
	 */
	private function persist( array $settings ): void {
		++self::$depth;

		try {
			$this->options_api->set( 'settings', $settings );
		} finally {
			--self::$depth;
		}
	}
}
