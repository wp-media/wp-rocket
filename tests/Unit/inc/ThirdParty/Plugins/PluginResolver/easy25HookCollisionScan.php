<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\PluginResolver;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Fixtures\classes\PluginResolverGatedIds;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\ConvertPlug;
use WP_Rocket\ThirdParty\Plugins\Cookie\Termly;
use WP_Rocket\ThirdParty\Plugins\I18n\TranslatePress;
use WP_Rocket\ThirdParty\Plugins\I18n\Weglot;
use WP_Rocket\ThirdParty\Plugins\InlineRelatedPosts;
use WP_Rocket\ThirdParty\Plugins\Jetpack;
use WP_Rocket\ThirdParty\Plugins\NGG;
use WP_Rocket\ThirdParty\Plugins\Optimization\Autoptimize;
use WP_Rocket\ThirdParty\Plugins\Optimization\Perfmatters;
use WP_Rocket\ThirdParty\Plugins\Optimization\RocketLazyLoad;
use WP_Rocket\ThirdParty\Plugins\Optimole;
use WP_Rocket\ThirdParty\Plugins\PageBuilder\BeaverBuilder;
use WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor;
use WP_Rocket\ThirdParty\Plugins\PDFEmbedder;
use WP_Rocket\ThirdParty\Plugins\PWA;
use WP_Rocket\ThirdParty\Plugins\Security\WordFenceCompatibility;
use WP_Rocket\ThirdParty\Plugins\SEO\RankMathSEO;
use WP_Rocket\ThirdParty\Plugins\SEO\SEOPress;
use WP_Rocket\ThirdParty\Plugins\SEO\TheSEOFramework;
use WP_Rocket\ThirdParty\Plugins\SEO\Yoast;
use WP_Rocket\ThirdParty\Plugins\SimpleCustomCss;
use WP_Rocket\ThirdParty\Plugins\SyntaxHighlighter;
use WP_Rocket\ThirdParty\Plugins\TheEventsCalendar;
use WP_Rocket\ThirdParty\Plugins\ThirstyAffiliates;
use WP_Rocket\ThirdParty\Plugins\UnlimitedElements;

/**
 * Verifies that none of the Easy-25 plugin-compat classes accidentally clash
 * on the same WordPress hook + priority.
 *
 * `get_subscribed_events()` is a static method with no WordPress/container
 * dependency, so it is called directly (`Class::get_subscribed_events()`) with
 * no instantiation involved. Most of the 25 classes return their hook map
 * unconditionally. Only 3 deviation classes still guard their hook map with a
 * business/feature-toggle check *inside* `get_subscribed_events()` itself, so
 * only these 3 need their "target plugin present" markers simulated to reveal
 * their real (maximal) hook set:
 * - SEOPress: needs `seopress_get_toggle_option('xml-sitemap')` and
 *   `seopress_get_service('SitemapOption')->isEnabled()` to both return 1,
 *   simulated with Brain\Monkey `Functions\when()`.
 * - TheSEOFramework: needs `the_seo_framework()` to return an object with a
 *   truthy `$loaded` and `can_run_sitemap()`; tests/Unit/bootstrap.php already
 *   preloads a fixture that declares this global with truthy values.
 * - Jetpack: needs a real `\Jetpack` class with `is_module_active('sitemaps')`
 *   truthy, stubbed via tests/Fixtures/classes/Jetpack.php. Declaring that
 *   global class requires `@runInSeparateProcess` isolation so it doesn't leak
 *   into the rest of the Unit suite; the Unit bootstrap has no WordPress
 *   DB/options layer for a forked process to corrupt.
 *
 * All 25 classes are otherwise driven with zero stubbing beyond the above — a
 * genuine capture of their real, unconditional hook maps, not a guess.
 *
 * @group ThirdParty
 * @group Plugins
 */
class Test_Easy25HookCollisionScan extends TestCase {

	/**
	 * (hook, priority) pairs registered by 2+ of the Easy-25 classes, reviewed
	 * and accepted as intentional. Keyed by "hook:priority".
	 *
	 * Only the `rocket_exclude_js:10` pair (elementor_subscriber vs
	 * syntaxhighlighter_subscriber) is documented elsewhere, by the
	 * `SubscriberFactory` registry-order comment. The remaining pairs are all
	 * instances of the same benign pattern: a WP Rocket "collector" filter (an
	 * exclusions or preload-list array that callbacks *append* to) that
	 * several plugin-compat classes legitimately contribute to. None of them
	 * mutate shared state in a way where registration order matters, unlike
	 * the documented pair.
	 *
	 * @var array<string,array<string>>
	 */
	private const ACCEPTED_COLLISIONS = [
		// Documented by SubscriberFactory's registry-order comment; this scan
		// also found pdfembedder on the same hook + priority (not mentioned there).
		'rocket_exclude_js:10'                      => [
			'elementor_subscriber',
			'pdfembedder',
			'syntaxhighlighter_subscriber',
		],
		// rocket_sitemap_preload_list is a collector array of sitemap URLs to
		// preload; every SEO-plugin compat class appends its own entry at
		// priority 15 (jetpack uses the default priority 10 instead, so it
		// does not collide with this group).
		'rocket_sitemap_preload_list:15'            => [
			'rank_math_seo',
			'seopress',
			'the_seo_framework',
			'yoast_seo',
		],
		// rocket_rucss_inline_content_exclusions is a collector array of RUCSS
		// inline-content exclusion patterns.
		'rocket_rucss_inline_content_exclusions:10' => [
			'inline_related_posts',
			'unlimited_elements',
		],
		// rocket_delay_js_exclusions is a collector array of delay-JS
		// exclusion patterns.
		'rocket_delay_js_exclusions:10'             => [
			'rocket_lazy_load',
			'termly_subscriber',
		],
		// rocket_exclude_defer_js is a collector array of defer-JS exclusion
		// patterns.
		'rocket_exclude_defer_js:10'                => [
			'syntaxhighlighter_subscriber',
			'termly_subscriber',
		],
	];

	/**
	 * Simulates the markers needed by the 3 deviation classes whose
	 * `get_subscribed_events()` still has its own internal guard.
	 *
	 * Only called from inside `@runInSeparateProcess` test methods: the
	 * Jetpack class declaration below is a real global that PHP can never
	 * "undeclare", so it must stay contained to its own forked, throwaway
	 * process rather than leaking into the rest of the Unit suite.
	 */
	private static function load_deviation_marker_stubs(): void {
		Functions\when( 'seopress_get_toggle_option' )->justReturn( 1 );
		Functions\when( 'seopress_get_service' )->justReturn(
			new class() {
				public function isEnabled() {
					return 1;
				}
			}
		);

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/classes/Jetpack.php';
	}

	/**
	 * Authoritative id => FQCN map for the Easy-25 batch, reconciled against
	 * PluginResolverGatedIds::IDS (the single source of truth for the gated
	 * ids, shared with the baseline equivalence test).
	 *
	 * @return array<string,string>
	 */
	private function easy_25_classes(): array {
		return [
			'elementor_subscriber'         => Elementor::class,
			'beaverbuilder_subscriber'     => BeaverBuilder::class,
			'simple_custom_css'            => SimpleCustomCss::class,
			'pdfembedder'                  => PDFEmbedder::class,
			'wordfence_subscriber'         => WordFenceCompatibility::class,
			'thirstyaffiliates'            => ThirstyAffiliates::class,
			'pwa'                          => PWA::class,
			'yoast_seo'                    => Yoast::class,
			'convertplug'                  => ConvertPlug::class,
			'unlimited_elements'           => UnlimitedElements::class,
			'inline_related_posts'         => InlineRelatedPosts::class,
			'jetpack'                      => Jetpack::class,
			'rank_math_seo'                => RankMathSEO::class,
			'seopress'                     => SEOPress::class,
			'the_seo_framework'            => TheSEOFramework::class,
			'rocket_lazy_load'             => RocketLazyLoad::class,
			'the_events_calendar'          => TheEventsCalendar::class,
			'perfmatters'                  => Perfmatters::class,
			'weglot'                       => Weglot::class,
			'translatepress'               => TranslatePress::class,
			'termly_subscriber'            => Termly::class,
			'optimole_subscriber'          => Optimole::class,
			'syntaxhighlighter_subscriber' => SyntaxHighlighter::class,
			'ngg_subscriber'               => NGG::class,
			'autoptimize'                  => Autoptimize::class,
		];
	}

	/**
	 * Sanity-check the enumeration itself: this scan must cover exactly the
	 * ids gated behind PluginCompatibilityInterface, no more, no less, so it
	 * can't silently drift out of sync with PluginResolverGatedIds::IDS.
	 */
	public function testShouldEnumerateExactlyTheEasy25GatedIds() {
		$ids = array_keys( $this->easy_25_classes() );

		sort( $ids );
		$expected = PluginResolverGatedIds::IDS;
		sort( $expected );

		$this->assertSame( $expected, $ids, 'The collision scan must cover exactly the Easy-25 gated ids, no more, no less.' );
	}

	/**
	 * The 3 deviation classes whose is_activated() presence oracle relies on
	 * the same marker this scan simulates for get_subscribed_events(): a
	 * quick consistency proof that the simulated markers genuinely represent
	 * "target plugin present", not just an accident of the guard's own logic.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldReportDeviationClassesActiveUnderSimulatedMarkers() {
		self::load_deviation_marker_stubs();

		$this->assertTrue( Jetpack::is_activated(), 'Jetpack::is_activated() should be true once the Jetpack stub class is loaded.' );
		$this->assertTrue( SEOPress::is_activated(), 'SEOPress::is_activated() should be true once seopress_get_toggle_option() is stubbed.' );
		$this->assertTrue( TheSEOFramework::is_activated(), 'TheSEOFramework::is_activated() should be true once the_seo_framework() is stubbed.' );
	}

	/**
	 * The core assertion: no WordPress hook is registered by 2+ of the Easy-25
	 * classes at the same priority, except the reviewed, documented pairs in
	 * self::ACCEPTED_COLLISIONS.
	 *
	 * Isolated (see load_deviation_marker_stubs()'s docblock): capturing
	 * Jetpack's real hook map requires declaring a real global class that
	 * must not leak into the rest of the suite.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldNotCollideOnHookAndPriorityExceptAcceptedPairs() {
		self::load_deviation_marker_stubs();

		$hook_priority_to_ids = [];

		foreach ( $this->easy_25_classes() as $id => $class ) {
			$events = $class::get_subscribed_events();

			foreach ( $events as $hook => $callback ) {
				foreach ( self::priorities_for_callback( $callback ) as $priority ) {
					$key = $hook . ':' . $priority;

					// Keyed by id to dedupe a class registering the same hook+priority
					// twice with itself (not a cross-class collision).
					$hook_priority_to_ids[ $key ][ $id ] = true;
				}
			}
		}

		$unexpected = [];

		foreach ( $hook_priority_to_ids as $key => $ids_map ) {
			$ids = array_keys( $ids_map );

			if ( count( $ids ) < 2 ) {
				continue;
			}

			sort( $ids );

			$allowed = self::ACCEPTED_COLLISIONS[ $key ] ?? null;

			if ( null !== $allowed ) {
				$allowed_sorted = $allowed;
				sort( $allowed_sorted );

				if ( $ids === $allowed_sorted ) {
					continue;
				}
			}

			$unexpected[] = sprintf( '%s registered by: %s', $key, implode( ', ', $ids ) );
		}

		$this->assertSame(
			[],
			$unexpected,
			"Unexpected hook/priority collision(s) among the Easy-25 batch (format 'hook:priority registered by: id1, id2'):\n" . implode( "\n", $unexpected )
		);
	}

	/**
	 * Normalizes a get_subscribed_events() callback value into the list of
	 * priorities it registers at, handling all 3 WP Rocket subscriber
	 * shapes:
	 * - 'hook' => 'method' (priority 10)
	 * - 'hook' => [ 'method', priority ] (or with a 3rd num-args element)
	 * - 'hook' => [ [ 'method', priority ], [ 'method2', priority2 ] ]
	 *
	 * @param mixed $callback The get_subscribed_events() value for one hook.
	 *
	 * @return array<int>
	 */
	private static function priorities_for_callback( $callback ): array {
		if ( is_string( $callback ) ) {
			return [ 10 ];
		}

		if ( ! is_array( $callback ) ) {
			return [ 10 ];
		}

		// Single-callback shorthand: [ method ] or [ method, priority ] or
		// [ method, priority, num_args ].
		if ( isset( $callback[0] ) && is_string( $callback[0] ) ) {
			return [ isset( $callback[1] ) ? (int) $callback[1] : 10 ];
		}

		// Multiple-callback shorthand: [ [ method, priority ], ... ].
		$priorities = [];

		foreach ( $callback as $sub_callback ) {
			$priorities = array_merge( $priorities, self::priorities_for_callback( $sub_callback ) );
		}

		return $priorities;
	}
}
