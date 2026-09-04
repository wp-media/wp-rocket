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
	 *
	 * Issue #8790 slice 2 additionally gates contactform7 (WPCF7_VERSION undefined)
	 * and cloudflare_plugin_subscriber (cloudflare/cloudflare.php not installed), so
	 * two more ids drop out. 43 - 4 - 2 + 2 = 39. Later slices of #8790 will lower
	 * this further as the remaining ids are gated.
	 *
	 * @var int
	 */
	private const EXPECTED_PLUGIN_SUBSCRIBERS = 39;

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
	 * Ids gated by issue #8790 slice 2 that report inactive in this test
	 * environment (neither Contact Form 7 nor the official Cloudflare plugin
	 * is installed), so they no longer default-active like the rest of the registry.
	 *
	 * @var array<string>
	 */
	private const SLICE_2_GATED_INACTIVE_IDS = [
		'contactform7',
		'cloudflare_plugin_subscriber',
	];

	/**
	 * Phase 0 defaults every registry id active; issue #8790 slices 1-2 opt 6 ids
	 * into real detection, so the resolver's set is the full registry minus
	 * those 6 (their target plugins are absent here), and the container must
	 * still resolve every remaining one of them.
	 */
	public function testShouldResolveEveryActivePluginIdFromTheLiveContainer() {
		$container = apply_filters( 'rocket_container', null );

		$this->assertNotNull( $container, 'The live WP Rocket container must be available via the rocket_container filter.' );

		$active_ids = PluginResolver::get_active_plugins( true );
		$registry   = ( new SubscriberFactory() )->get_registry();

		$expected_active_ids = array_values(
			array_diff(
				array_keys( $registry ),
				array_merge( self::SLICE_1_GATED_INACTIVE_IDS, self::SLICE_2_GATED_INACTIVE_IDS )
			)
		);

		$this->assertSame( $expected_active_ids, $active_ids, 'Phase 1 slice 2 must resolve to the 43-id registry minus the 6 gated-inactive ids.' );
		$this->assertCount( 37, $active_ids );

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

		// 37 active resolver ids (43 - 4 slice-1-gated - 2 slice-2-gated) + ezoic + mod_pagespeed = 39.
		$this->assertSame( self::EXPECTED_PLUGIN_SUBSCRIBERS, count( $active_ids ) + 2 );
	}
}
