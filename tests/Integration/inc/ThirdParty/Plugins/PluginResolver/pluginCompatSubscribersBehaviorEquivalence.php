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
	 * The 44-id factory registry minus the 7 gated-inactive ids in this test
	 * environment, plus the 2 statically-registered ids (ezoic, mod_pagespeed).
	 *
	 * @var int
	 */
	private const EXPECTED_PLUGIN_SUBSCRIBERS = 39;

	/**
	 * Registry ids that report inactive in this test environment: their target
	 * plugin is absent, or a gate such as is_admin() is not met.
	 *
	 * @var array<string>
	 */
	private const GATED_INACTIVE_IDS = [
		'revolution_slider_subscriber',
		'optimus_webp_subscriber',
		'rapidload',
		'all_in_one_seo_pack',
		'contactform7',
		'cloudflare_plugin_subscriber',
		'hummingbird_subscriber',
	];

	/**
	 * The resolver's active set is the full registry minus the ids whose
	 * target plugins aren't installed, or whose gate condition (e.g.
	 * is_admin()) isn't met, here, and the container must resolve every
	 * remaining one of them.
	 */
	public function testShouldResolveEveryActivePluginIdFromTheLiveContainer() {
		$container = apply_filters( 'rocket_container', null );

		$this->assertNotNull( $container, 'The live WP Rocket container must be available via the rocket_container filter.' );

		$active_ids = PluginResolver::get_active_plugins( true );
		$registry   = ( new SubscriberFactory() )->get_registry();

		$expected_active_ids = array_values( array_diff( array_keys( $registry ), self::GATED_INACTIVE_IDS ) );

		$this->assertSame( $expected_active_ids, $active_ids, 'The resolver must report the registry ids minus the gated-inactive ones.' );
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

		// 37 active resolver ids + ezoic + mod_pagespeed = 39 (see EXPECTED_PLUGIN_SUBSCRIBERS docblock).
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
