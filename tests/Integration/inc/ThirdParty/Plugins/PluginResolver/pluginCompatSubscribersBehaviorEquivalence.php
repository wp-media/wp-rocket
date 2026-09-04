<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\PluginResolver;

use ReflectionMethod;
use WP_Rocket\Tests\Fixtures\classes\PluginResolverActivePlugin;
use WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare;
use WP_Rocket\ThirdParty\Plugins\Optimization\Hummingbird;
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
	 * two more ids drop out. 43 - 4 - 2 + 2 = 39.
	 *
	 * Issue #8790 slice 3 moves hummingbird_subscriber INTO the 43-id factory
	 * registry (previously it was wired outside it, unconditionally, via
	 * Engine/Admin/ServiceProvider, and was never counted in this 45/43 baseline
	 * at all). The registry therefore grows to 44 ids, but hummingbird_subscriber
	 * is immediately gated back out here (is_admin() is false in this non-admin
	 * test context), so the net effect on this count is zero: (44 - 4 - 2 - 1) + 2
	 * = 39, unchanged from slice 2. The count does not drop to 38 as a naive
	 * "one more id gated" reading would suggest, because hummingbird_subscriber
	 * was never part of the "43 factory ids" this baseline counts in the first
	 * place — moving it in and immediately excluding it nets to no change.
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
	 * Id gated by issue #8790 slice 3 that reports inactive in this test
	 * environment (is_admin() is false in this non-admin integration test
	 * context), so it no longer defaults-active like the rest of the registry.
	 * hummingbird_subscriber also newly appears in the registry itself in
	 * slice 3 (previously wired outside it via Engine/Admin/ServiceProvider).
	 *
	 * @var array<string>
	 */
	private const SLICE_3_GATED_INACTIVE_IDS = [
		'hummingbird_subscriber',
	];

	/**
	 * Phase 0 defaults every registry id active; issue #8790 slices 1-3 opt 7 ids
	 * into real detection, so the resolver's set is the full (now 44-id) registry
	 * minus those 7 (their target plugins are absent, or is_admin() is false,
	 * here), and the container must still resolve every remaining one of them.
	 */
	public function testShouldResolveEveryActivePluginIdFromTheLiveContainer() {
		$container = apply_filters( 'rocket_container', null );

		$this->assertNotNull( $container, 'The live WP Rocket container must be available via the rocket_container filter.' );

		$active_ids = PluginResolver::get_active_plugins( true );
		$registry   = ( new SubscriberFactory() )->get_registry();

		$expected_active_ids = array_values(
			array_diff(
				array_keys( $registry ),
				array_merge( self::SLICE_1_GATED_INACTIVE_IDS, self::SLICE_2_GATED_INACTIVE_IDS, self::SLICE_3_GATED_INACTIVE_IDS )
			)
		);

		$this->assertSame( $expected_active_ids, $active_ids, 'Phase 1 slice 3 must resolve to the 44-id registry minus the 7 gated-inactive ids.' );
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

		// 37 active resolver ids (44 - 4 slice-1-gated - 2 slice-2-gated - 1 slice-3-gated)
		// + ezoic + mod_pagespeed = 39. Unchanged from slice 2 (see EXPECTED_PLUGIN_SUBSCRIBERS docblock).
		$this->assertSame( self::EXPECTED_PLUGIN_SUBSCRIBERS, count( $active_ids ) + 2 );
	}

	/**
	 * Id-set-equivalence proof (issue #8790 slice 3, final): the resolver
	 * includes any registry id whose class reports itself active, regardless
	 * of which ids those are, and hummingbird_subscriber is no exception once
	 * it lives in the shared registry. Using stub classes for the "active"
	 * side (rather than defining the 5 constant-based plugins' real global
	 * constants) keeps this test free of cross-test global-state pollution;
	 * each real class's own is_activated() logic is already exhaustively
	 * covered by its dedicated Unit test.
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
	 * is (simulated) present — the exact scenario the user's is_admin() gate
	 * decision is meant to guard against (WP-CLI/cron/front-end contexts).
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
	 * Hook-collision proof (issue #8790 slice 3, final): the resolver never
	 * reorders registry ids — it only filters them in-place — so whichever
	 * subset happens to be simultaneously active, their relative registration
	 * order (and therefore same-priority hook execution order) matches the
	 * registry's declared order. This also confirms the documented
	 * syntaxhighlighter_subscriber/elementor_subscriber ordering, and the new
	 * hummingbird_subscriber entry appended at the end of the registry, do not
	 * disturb any other id's relative position.
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
	 * full, unrelated 44-id production registry.
	 *
	 * @param array<string,string> $registry Id => FQCN map.
	 *
	 * @return array<string>
	 */
	private function invoke_filter_active_registry( array $registry ): array {
		$method = new ReflectionMethod( PluginResolver::class, 'filter_active_registry' );
		$method->setAccessible( true );

		return $method->invoke( null, $registry );
	}
}
