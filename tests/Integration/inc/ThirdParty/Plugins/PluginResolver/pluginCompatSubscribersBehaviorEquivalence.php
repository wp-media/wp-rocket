<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\PluginResolver;

use WP_Rocket\ThirdParty\Plugins\PluginResolver;
use WP_Rocket\ThirdParty\Plugins\SubscriberFactory;
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
	 * Issue #8790 slice 1 gates 4 of the 43 registry ids (revolution_slider_subscriber,
	 * optimus_webp_subscriber, rapidload, all_in_one_seo_pack) behind
	 * PluginCompatibilityInterface; none of their target plugins are installed in
	 * this test environment, so they drop out of get_active_plugins(). 43 - 4 + 2 = 41.
	 * Later slices of #8790 will lower this further as the remaining ids are gated.
	 *
	 * @var int
	 */
	private const EXPECTED_PLUGIN_SUBSCRIBERS = 41;

	/**
	 * Ids gated by issue #8790 slice 1 that report inactive in this test
	 * environment (none of their target plugins are installed), so they no
	 * longer default-active like the rest of the registry.
	 *
	 * @var array<string>
	 */
	private const SLICE_1_GATED_INACTIVE_IDS = [
		'revolution_slider_subscriber',
		'optimus_webp_subscriber',
		'rapidload',
		'all_in_one_seo_pack',
	];

	/**
	 * Phase 0 defaults every registry id active; issue #8790 slice 1 opts 4 ids
	 * into real detection, so the resolver's set is the full registry minus
	 * those 4 (their target plugins are absent here), and the container must
	 * still resolve every remaining one of them.
	 */
	public function testShouldResolveEveryActivePluginIdFromTheLiveContainer() {
		$container = apply_filters( 'rocket_container', null );

		$this->assertNotNull( $container, 'The live WP Rocket container must be available via the rocket_container filter.' );

		$active_ids = PluginResolver::get_active_plugins( true );
		$registry   = ( new SubscriberFactory() )->get_registry();

		$expected_active_ids = array_values( array_diff( array_keys( $registry ), self::SLICE_1_GATED_INACTIVE_IDS ) );

		$this->assertSame( $expected_active_ids, $active_ids, 'Phase 1 slice 1 must resolve to the 43-id registry minus the 4 gated-inactive ids.' );
		$this->assertCount( 39, $active_ids );

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
	 * cloudflare_plugin_subscriber. All four must resolve cleanly.
	 */
	public function testShouldResolveThePreviouslyDriftingAndDependencyIds() {
		$container = apply_filters( 'rocket_container', null );

		foreach ( [ 'yoast_seo', 'thirstyaffiliates', 'convertplug', 'cloudflare_plugin_facade' ] as $id ) {
			$this->assertTrue( $container->has( $id ), "Expected container to provide '{$id}'." );
			$this->assertIsObject( $container->get( $id ) );
		}
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

		// 39 active resolver ids (43 - 4 slice-1-gated) + ezoic + mod_pagespeed = 41.
		$this->assertSame( self::EXPECTED_PLUGIN_SUBSCRIBERS, count( $active_ids ) + 2 );
	}
}
