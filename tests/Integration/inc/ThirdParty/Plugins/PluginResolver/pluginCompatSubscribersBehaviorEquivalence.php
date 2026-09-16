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
	 * Baseline: the 43 factory-owned ids + the 2 statically-registered special
	 * ids (ezoic, mod_pagespeed) = the 45 plugin-compat ids previously hardcoded
	 * in Plugin::$common_subscribers.
	 *
	 * Issue #8795 adds 9 newly-ported registry ids and gates them behind
	 * PluginCompatibilityInterface. Because they are NEW ids (not existing ones
	 * being gated), and none of their target plugins are installed here, the
	 * active count is unchanged: each of the 9 is removed from the resolved set by
	 * array_diff() against PluginResolverGatedIds::IDS, leaving the original 43
	 * still active. 43 + 2 = 45.
	 *
	 * @var int
	 */
	private const EXPECTED_PLUGIN_SUBSCRIBERS = 45;

	/**
	 * Phase 0 defaulted every registry id active; issue #8795 opts 9 newly-ported
	 * ids into real detection, so the resolver's set is the registry minus those 9
	 * (their target plugins are absent here), which equals the original 43 ids, and
	 * the container must still resolve every one of them.
	 */
	public function testShouldResolveEveryActivePluginIdFromTheLiveContainer() {
		$container = apply_filters( 'rocket_container', null );

		$this->assertNotNull( $container, 'The live WP Rocket container must be available via the rocket_container filter.' );

		$active_ids = PluginResolver::get_active_plugins( true );
		$registry   = ( new SubscriberFactory() )->get_registry();

		$expected_active_ids = array_values( array_diff( array_keys( $registry ), PluginResolverGatedIds::IDS ) );

		$this->assertSame( $expected_active_ids, $active_ids, 'Resolver must return the registry minus the 9 gated-inactive #8795 ids.' );
		$this->assertCount( 43, $active_ids );

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

		// 43 active resolver ids (the 9 newly-ported #8795 ids are gated-inactive
		// here) + ezoic + mod_pagespeed = 45.
		$this->assertSame( self::EXPECTED_PLUGIN_SUBSCRIBERS, count( $active_ids ) + 2 );
	}
}
