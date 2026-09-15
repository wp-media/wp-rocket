<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\PluginResolver;

use WP_Rocket\ThirdParty\Plugins\PluginResolver;
use WP_Rocket\ThirdParty\Plugins\SubscriberFactory;
use WP_Rocket\Tests\Fixtures\classes\PluginResolverGatedIds;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Behavior-equivalence proof for issue #6418 Phase 0: the plugin resolver
 * scaffolding must reach the event manager with the identical set of
 * plugin-compat subscriber ids as the pre-refactor static list.
 *
 * @group ThirdParty
 * @group Plugins
 */
class Test_PluginCompatSubscribersBehaviorEquivalence extends TestCase {
	/**
	 * Baseline: originally the 43 factory-owned ids + the 2 statically-registered
	 * special ids (ezoic, mod_pagespeed) = the 45 plugin-compat ids previously
	 * hardcoded in Plugin::$common_subscribers.
	 *
	 * Issue #8789 slices 1-2 gate 16 of the 43 registry ids (slice 1: elementor_subscriber,
	 * beaverbuilder_subscriber, simple_custom_css, pdfembedder, wordfence_subscriber,
	 * unlimited_elements, inline_related_posts; slice 2: rank_math_seo, rocket_lazy_load,
	 * the_events_calendar, perfmatters, weglot, translatepress, termly_subscriber,
	 * optimole_subscriber, convertplug) behind PluginCompatibilityInterface; none of their
	 * target plugins are installed in this test environment, so they drop out of
	 * get_active_plugins(). 43 - 16 + 2 = 29. Later #8789 slices will lower this further
	 * as the remaining ids are gated.
	 *
	 * @var int
	 */
	private const EXPECTED_PLUGIN_SUBSCRIBERS = 29;

	/**
	 * Phase 0 defaults every registry id active; issue #8789 slices 1-2 opt 16 ids
	 * into real detection, so the resolver's set is the full registry minus
	 * those 16 (their target plugins are absent here), and the container must
	 * still resolve every remaining one of them.
	 */
	public function testShouldResolveEveryActivePluginIdFromTheLiveContainer() {
		$container = apply_filters( 'rocket_container', null );

		$this->assertNotNull( $container, 'The live WP Rocket container must be available via the rocket_container filter.' );

		$active_ids = PluginResolver::get_active_plugins( true );
		$registry   = ( new SubscriberFactory() )->get_registry();

		$expected_active_ids = array_values( array_diff( array_keys( $registry ), PluginResolverGatedIds::IDS ) );

		$this->assertSame( $expected_active_ids, $active_ids, 'Phase 1 slices 1-2 must resolve to the 43-id registry minus the 16 gated-inactive ids.' );
		$this->assertCount( 27, $active_ids );

		foreach ( $active_ids as $id ) {
			$this->assertTrue(
				$container->has( $id ),
				"Expected container to provide '{$id}'."
			);

			$service = $container->get( $id );

			$this->assertIsObject( $service, "Expected container->get( '{$id}' ) to resolve to an object." );
		}
	}

	/**
	 * Drift/dedup proof: yoast_seo and thirstyaffiliates were previously
	 * missing from $provides (drift), and convertplug was double-registered.
	 * cloudflare_plugin_facade is the internal dependency of
	 * cloudflare_plugin_subscriber. yoast_seo, thirstyaffiliates, and
	 * cloudflare_plugin_facade are not yet gated (still un-migrated or a
	 * `->add()` dependency, not a registry id) so they must still resolve.
	 */
	public function testShouldResolveThePreviouslyDriftingAndDependencyIds() {
		$container = apply_filters( 'rocket_container', null );

		foreach ( [ 'yoast_seo', 'thirstyaffiliates', 'cloudflare_plugin_facade' ] as $id ) {
			$this->assertTrue( $container->has( $id ), "Expected container to provide '{$id}'." );
			$this->assertIsObject( $container->get( $id ) );
		}
	}

	/**
	 * Correctness proof (was a drift-proof pre-#8789): convertplug is gated by
	 * issue #8789 slice 2, and its target plugin (CP_VERSION) is not installed
	 * in this test environment, so it must now correctly resolve to absent
	 * instead of being force-registered regardless of activation state.
	 */
	public function testShouldNotResolveConvertPlugWhenAbsent() {
		$container = apply_filters( 'rocket_container', null );

		$this->assertFalse( $container->has( 'convertplug' ), 'Expected container to NOT provide "convertplug" when CP_VERSION is undefined.' );
	}

	/**
	 * SPECIAL always-load proof: ezoic and mod_pagespeed stay statically
	 * registered regardless of the resolver's output.
	 */
	public function testShouldAlwaysResolveEzoicAndModPagespeed() {
		$container = apply_filters( 'rocket_container', null );

		foreach ( [ 'ezoic', 'mod_pagespeed' ] as $id ) {
			$this->assertTrue( $container->has( $id ), "Expected container to provide '{$id}'." );
			$this->assertIsObject( $container->get( $id ) );
		}
	}

	/**
	 * Perf-baseline harness: records the expected plugin-compat subscriber
	 * count so Phase 1 (which drops inactive ids) updates it deliberately.
	 */
	public function testShouldMatchThePluginSubscriberCountBaseline() {
		$active_ids = PluginResolver::get_active_plugins( true );

		// 27 active resolver ids (43 - 16 slice-1/2-gated) + ezoic + mod_pagespeed = 29.
		$this->assertSame( self::EXPECTED_PLUGIN_SUBSCRIBERS, count( $active_ids ) + 2 );
	}
}
