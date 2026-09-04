<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\PluginResolver;

use WP_Rocket\Tests\Fixtures\classes\PluginResolverActivePlugin;
use WP_Rocket\Tests\Fixtures\classes\PluginResolverGatedIds;
use WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare;
use WP_Rocket\ThirdParty\Plugins\Optimization\Hummingbird;
use WP_Rocket\ThirdParty\Plugins\PluginResolver;
use WP_Rocket\ThirdParty\Plugins\SubscriberFactory;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Verifies the plugin resolver's active-id set is exactly reflected in the
 * live container: every id the resolver reports active must resolve to an
 * object, and every id gated behind PluginCompatibilityInterface (whose
 * target plugin is absent in this test environment) must resolve to absent
 * rather than being force-registered.
 *
 * The hook-collision half of this coverage lives in the Unit suite instead
 * (tests/Unit/inc/ThirdParty/Plugins/PluginResolver/hookCollisionScan.php):
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
	private const EXPECTED_PLUGIN_SUBSCRIBERS = 14;

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

	/**
	 * Id-set-equivalence proof: the resolver includes any registry id whose
	 * class reports itself active, regardless of which ids those are. Using
	 * stub classes for the "active" side (rather than defining the 5
	 * constant-based plugins' real global constants) keeps this test free of
	 * cross-test global-state pollution; each real class's own
	 * is_activated() logic is already exhaustively covered by its dedicated
	 * Unit test.
	 */
	public function testShouldIncludeAnyRegistryIdWhoseClassReportsItselfActive() {
		$registry = ( new SubscriberFactory() )->get_registry();

		$simulated_active_ids = [
			'revolution_slider_subscriber',
			'optimus_webp_subscriber',
			'rapidload',
			'all_in_one_seo_pack',
			'contactform7',
			'cloudflare_plugin_subscriber',
			'hummingbird_subscriber',
		];

		$synthetic_registry = $registry;

		foreach ( $simulated_active_ids as $id ) {
			$synthetic_registry[ $id ] = PluginResolverActivePlugin::class;
		}

		$active = $this->invoke_filter_active_registry( $synthetic_registry );

		foreach ( $simulated_active_ids as $id ) {
			$this->assertContains( $id, $active, "Expected '{$id}' to be included once its class reports itself active." );
		}
	}

	/**
	 * Id-set-equivalence proof: hummingbird_subscriber's real is_activated()
	 * excludes it whenever is_admin() is false, even when the plugin itself
	 * is (simulated) present, as in WP-CLI, cron and front-end contexts.
	 */
	public function testShouldExcludeHummingbirdWhenNotAdminEvenIfPluginPresent() {
		add_filter( 'pre_option_active_plugins', [ $this, 'fakeHummingbirdActivePlugin' ] );

		set_current_screen( 'front' );

		$active = $this->invoke_filter_active_registry( [ 'hummingbird_subscriber' => Hummingbird::class ] );

		remove_filter( 'pre_option_active_plugins', [ $this, 'fakeHummingbirdActivePlugin' ] );

		$this->assertSame( [], $active, 'hummingbird_subscriber must stay excluded outside of admin, even when the plugin is present.' );
	}

	/**
	 * Symmetric proof: with is_admin() true and the plugin present,
	 * hummingbird_subscriber's real is_activated() does include it.
	 */
	public function testShouldIncludeHummingbirdWhenAdminAndPluginPresent() {
		add_filter( 'pre_option_active_plugins', [ $this, 'fakeHummingbirdActivePlugin' ] );

		set_current_screen( 'settings_page_wprocket' );

		$active = $this->invoke_filter_active_registry( [ 'hummingbird_subscriber' => Hummingbird::class ] );

		set_current_screen( 'front' );
		remove_filter( 'pre_option_active_plugins', [ $this, 'fakeHummingbirdActivePlugin' ] );

		$this->assertSame( [ 'hummingbird_subscriber' ], $active );
	}

	/**
	 * Presence-only regression proof, extended to the shared registry: Cloudflare's
	 * is_activated() must never be influenced by is_admin(), unlike Hummingbird.
	 */
	public function testShouldIncludeCloudflareRegardlessOfAdminContext() {
		add_filter( 'pre_option_active_plugins', [ $this, 'fakeCloudflareActivePlugin' ] );

		set_current_screen( 'front' );

		$active = $this->invoke_filter_active_registry( [ 'cloudflare_plugin_subscriber' => Cloudflare::class ] );

		remove_filter( 'pre_option_active_plugins', [ $this, 'fakeCloudflareActivePlugin' ] );

		$this->assertSame( [ 'cloudflare_plugin_subscriber' ], $active );
	}

	/**
	 * Hook-collision proof: the resolver never reorders registry ids — it
	 * only filters them in-place — so whichever subset happens to be
	 * simultaneously active, their relative registration order (and
	 * therefore same-priority hook execution order) matches the registry's
	 * declared order, including for syntaxhighlighter_subscriber /
	 * elementor_subscriber and hummingbird_subscriber.
	 */
	public function testShouldPreserveRegistryDeclaredOrderForAnyActiveSubset() {
		$registry = ( new SubscriberFactory() )->get_registry();
		$ids      = array_keys( $registry );

		$syntaxhighlighter_position = array_search( 'syntaxhighlighter_subscriber', $ids, true );
		$elementor_position         = array_search( 'elementor_subscriber', $ids, true );

		$this->assertLessThan(
			$elementor_position,
			$syntaxhighlighter_position,
			'syntaxhighlighter_subscriber must remain registered before elementor_subscriber.'
		);

		$simulated_active_ids = [ 'syntaxhighlighter_subscriber', 'elementor_subscriber', 'hummingbird_subscriber' ];
		$synthetic_registry   = $registry;

		foreach ( $simulated_active_ids as $id ) {
			$synthetic_registry[ $id ] = PluginResolverActivePlugin::class;
		}

		$active           = $this->invoke_filter_active_registry( $synthetic_registry );
		$active_positions = array_map(
			static function ( $id ) use ( $ids ) {
				return array_search( $id, $ids, true );
			},
			$active
		);
		$sorted_positions = $active_positions;
		sort( $sorted_positions );

		$this->assertSame( $sorted_positions, $active_positions, 'PluginResolver must preserve the registry declared order.' );
	}

	/**
	 * Fakes the active_plugins option so is_plugin_active() reports the
	 * official Hummingbird basename as active, without installing the plugin.
	 *
	 * @return array
	 */
	public function fakeHummingbirdActivePlugin() {
		return [ 'hummingbird-performance/wp-hummingbird.php' ];
	}

	/**
	 * Fakes the active_plugins option so is_plugin_active() reports the
	 * official Cloudflare basename as active, without installing the plugin.
	 *
	 * @return array
	 */
	public function fakeCloudflareActivePlugin() {
		return [ 'cloudflare/cloudflare.php' ];
	}

	/**
	 * Invokes PluginResolver's private filter_active_registry() for a given
	 * synthetic registry, bypassing get_active_plugins()'s memoization and the
	 * full production registry.
	 *
	 * @param array<string,string> $registry Id => FQCN map.
	 *
	 * @return array<string>
	 */
	private function invoke_filter_active_registry( array $registry ): array {
		return $this->get_reflective_method( 'filter_active_registry', PluginResolver::class )->invoke( null, $registry );
	}
}
