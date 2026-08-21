<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN;

use WP_Rocket\Admin\Options;
use WP_Rocket\Engine\Common\Utils;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Keeps the legacy `cdn` / `cdn_type` fields and the `cdn_state` option in agreement while
 * both are still written independently across the codebase.
 *
 * This is temporary scaffolding for the RocketCDN refactor epic (#8693): it lets stories land
 * and ship one at a time without every write path needing to be migrated in lockstep. Its
 * removal criterion is Story 10 - once nothing reads the legacy fields directly any more, this
 * class and CdnStateTranslator can both be deleted along with them.
 *
 * It does no more than reconcile option shapes: no cache clearing, no API calls. Those stay the
 * sole responsibility of whatever already reacts to `update_option_wp_rocket_settings` at a
 * later priority (e.g. Subscriber::maybe_clear_cache()), which this class runs ahead of so it
 * always sees a settled cdn_state.
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
	 * Reconciles the legacy fields and cdn_state after a settings save.
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

		$legacy_changed = Utils::did_setting_change( 'cdn', $old_value, $value )
			|| Utils::did_setting_change( 'cdn_type', $old_value, $value );

		if ( $legacy_changed ) {
			$this->sync_legacy_to_state( $value );
			return;
		}

		if ( Utils::did_setting_change( 'cdn_state', $old_value, $value ) ) {
			$this->sync_state_to_legacy( $value );
		}
	}

	/**
	 * Corrects cdn_state to match the legacy fields that just changed.
	 *
	 * @param array $value New wp_rocket_settings value.
	 *
	 * @return void
	 */
	private function sync_legacy_to_state( array $value ): void {
		$expected_state = $this->translator->legacy_to_state( $value );
		$actual_state   = (string) ( $value['cdn_state'] ?? Context::CDN_STATE_NOTHING );

		if ( $expected_state === $actual_state ) {
			return;
		}

		$value['cdn_state'] = $expected_state;

		$this->persist( $value );
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
