<?php

namespace WP_Rocket\Tests\Integration;

/**
 * Resets the memoized state on the shared CDN driver/Subscriber container singletons.
 *
 * `cdn_driver_byocdn` (Custom) and `cdn_subscriber` are both `addShared()` container
 * singletons, so `CdnHostnameTrait`'s per-instance hostname/should-rewrite memo and the
 * Subscriber's own lazily-resolved driver memo would otherwise leak from one test/data set
 * into the next — e.g. a hostname-present answer computed for one dataset's `cdn_cnames`
 * would incorrectly apply to a later dataset in the same test run.
 *
 * Used by every integration test that switches `cdn_cnames`/`cdn_type` between data sets
 * within the same container instance (test classes are expected to also provide
 * `set_reflective_property()`, available via `WPMedia\PHPUnit\Integration\TestCase`).
 */
trait ResetsCdnDriverStateTrait {

	/**
	 * @return void
	 */
	protected function reset_cdn_driver_memo(): void {
		$container = apply_filters( 'rocket_container', null );

		$driver = $container->get( 'cdn_driver_byocdn' );
		$this->set_reflective_property( null, 'has_hostname_memo', $driver );
		$this->set_reflective_property( [], 'should_rewrite_memo', $driver );

		$subscriber = $container->get( 'cdn_subscriber' );
		$this->set_reflective_property( null, 'driver', $subscriber );
	}
}
