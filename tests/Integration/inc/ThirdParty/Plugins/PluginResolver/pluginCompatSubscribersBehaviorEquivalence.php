<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\PluginResolver;

use WP_Rocket\ThirdParty\Plugins\PluginResolver;
use WP_Rocket\ThirdParty\Plugins\SubscriberFactory;
use WP_Rocket\Tests\Fixtures\classes\PluginResolverGatedIds;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Verifies the plugin resolver's active-id set is exactly reflected in the
 * live container: every id the resolver reports active must resolve to an
 * object, and every id gated behind PluginCompatibilityInterface (whose
 * target plugin is absent in this test environment) must resolve to absent
 * rather than being force-registered.
 *
 * The hook-collision half of this coverage lives in the Unit suite instead
 * (tests/Unit/inc/ThirdParty/Plugins/PluginResolver/easy25HookCollisionScan.php):
 * `get_subscribed_events()` is a static method with no WordPress dependency,
 * and its `@runInSeparateProcess` isolation is incompatible with the shared
 * Integration bootstrap.
 *
 * @group ThirdParty
 * @group Plugins
 */
class Test_PluginCompatSubscribersBehaviorEquivalence extends TestCase {
	/**
	 * Total plugin-compat subscribers expected in the live container: the
	 * registry ids that are not gated behind PluginCompatibilityInterface, plus
	 * the 2 statically-registered special ids (ezoic, mod_pagespeed) that bypass
	 * the resolver entirely. Update this constant whenever the gated-id list or
	 * the registry size changes.
	 *
	 * @var int
	 */
	private const EXPECTED_PLUGIN_SUBSCRIBERS = 20;

	/**
	 * Every id the resolver reports as active (the full registry minus the ids
	 * gated behind PluginCompatibilityInterface) must resolve to an object
	 * through the live container.
	 */
	public function testShouldResolveEveryActivePluginIdFromTheLiveContainer() {
		$container = apply_filters( 'rocket_container', null );

		$this->assertNotNull( $container, 'The live WP Rocket container must be available via the rocket_container filter.' );

		$active_ids = PluginResolver::get_active_plugins( true );
		$registry   = ( new SubscriberFactory() )->get_registry();

		$expected_active_ids = array_values( array_diff( array_keys( $registry ), PluginResolverGatedIds::IDS ) );

		$this->assertSame( $expected_active_ids, $active_ids, 'Must resolve to the full registry minus the gated-inactive ids.' );

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
	 * cloudflare_plugin_facade is an internal `->add()` dependency of
	 * cloudflare_plugin_subscriber, not a registry id, so it is never gated and
	 * must always resolve.
	 */
	public function testShouldResolveThePreviouslyDriftingAndDependencyIds() {
		$container = apply_filters( 'rocket_container', null );

		foreach ( [ 'cloudflare_plugin_facade' ] as $id ) {
			$this->assertTrue( $container->has( $id ), "Expected container to provide '{$id}'." );
			$this->assertIsObject( $container->get( $id ) );
		}
	}

	/**
	 * convertplug is gated behind PluginCompatibilityInterface; its target
	 * constant (CP_VERSION) is undefined in this test environment, so it must
	 * resolve to absent.
	 */
	public function testShouldNotResolveConvertPlugWhenAbsent() {
		$container = apply_filters( 'rocket_container', null );

		$this->assertFalse( $container->has( 'convertplug' ), 'Expected container to NOT provide "convertplug" when CP_VERSION is undefined.' );
	}

	/**
	 * yoast_seo and thirstyaffiliates are gated behind
	 * PluginCompatibilityInterface; neither target plugin (WPSEO_VERSION /
	 * thirstyaffiliates/thirstyaffiliates.php) is present in this test
	 * environment, so both must resolve to absent.
	 */
	public function testShouldNotResolveYoastOrThirstyAffiliatesWhenAbsent() {
		$container = apply_filters( 'rocket_container', null );

		foreach ( [ 'yoast_seo', 'thirstyaffiliates' ] as $id ) {
			$this->assertFalse( $container->has( $id ), "Expected container to NOT provide '{$id}' when its target plugin is absent." );
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
	 * Baseline check: the active resolver ids plus the 2 special always-load
	 * ids (ezoic, mod_pagespeed) must match EXPECTED_PLUGIN_SUBSCRIBERS.
	 */
	public function testShouldMatchThePluginSubscriberCountBaseline() {
		$active_ids = PluginResolver::get_active_plugins( true );

		// +2 accounts for ezoic and mod_pagespeed, which bypass the resolver.
		$this->assertSame( self::EXPECTED_PLUGIN_SUBSCRIBERS, count( $active_ids ) + 2 );
	}
}
