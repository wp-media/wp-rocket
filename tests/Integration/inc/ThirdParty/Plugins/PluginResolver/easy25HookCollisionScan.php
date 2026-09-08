<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\PluginResolver;

use WP_Rocket\Subscriber\Third_Party\Plugins\NGG_Subscriber;
use WP_Rocket\Subscriber\Third_Party\Plugins\SyntaxHighlighter_Subscriber;
use WP_Rocket\Tests\Fixtures\classes\PluginResolverGatedIds;
use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\ThirdParty\Plugins\ConvertPlug;
use WP_Rocket\ThirdParty\Plugins\Cookie\Termly;
use WP_Rocket\ThirdParty\Plugins\I18n\TranslatePress;
use WP_Rocket\ThirdParty\Plugins\I18n\Weglot;
use WP_Rocket\ThirdParty\Plugins\InlineRelatedPosts;
use WP_Rocket\ThirdParty\Plugins\Jetpack;
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
use WP_Rocket\ThirdParty\Plugins\TheEventsCalendar;
use WP_Rocket\ThirdParty\Plugins\ThirstyAffiliates;
use WP_Rocket\ThirdParty\Plugins\UnlimitedElements;

/**
 * AC-required hook-collision scan for issue #8789's Easy-25 batch (slices 1-4):
 * proves that the 25 migrated classes don't accidentally clash on the same
 * WordPress hook + priority once they're all resolver-gated.
 *
 * Methodology: `get_subscribed_events()` is a *static* method on every one of
 * the 25 classes, so it is called directly (`Class::get_subscribed_events()`),
 * with no container/instantiation involved. 22 of the 25 classes already
 * return their hook map unconditionally — issue #8789 slices 1-4 moved their
 * presence guard out of `get_subscribed_events()` and into `is_activated()`
 * (Implementation Plan §1 step 3 / §2's "leave get_subscribed_events()
 * untouched" rule for the 7 deviation classes), so no "target plugin present"
 * simulation is needed for them at all: the guard that used to gate the
 * return value is simply gone.
 *
 * Only 3 of the 7 deviation classes still have a guard *inside*
 * `get_subscribed_events()` itself (their business/feature-toggle checks,
 * deliberately left untouched per §2), so only these 3 need their markers
 * simulated to reveal their real (maximal) hook set:
 * - Jetpack: needs a real `\Jetpack` class + `is_module_active('sitemaps')`
 *   truthy. Stubbed via tests/Fixtures/classes/Jetpack.php (new fixture,
 *   mirrors the TRP_Translate_Press.php stub-class convention already in
 *   that directory).
 * - SEOPress: needs `seopress_get_toggle_option('xml-sitemap')` to return 1
 *   AND `seopress_get_service('SitemapOption')->isEnabled()` to return 1.
 *   Reuses the existing tests/Fixtures/inc/ThirdParty/Plugins/SEO/SEOPress/fixtures.php
 *   (already declares these guarded globally, no override needed).
 * - TheSEOFramework: needs `the_seo_framework()` to return an object with a
 *   truthy `$loaded` and a truthy `can_run_sitemap()`. Reuses the existing
 *   tests/Fixtures/inc/ThirdParty/Plugins/SEO/TheSEOFramework/fixtures.php.
 *
 * All 25 classes are otherwise driven with zero stubbing — a genuine capture
 * of their real, unconditional hook maps, not a guess.
 *
 * @group ThirdParty
 * @group Plugins
 */
class Test_Easy25HookCollisionScan extends TestCase {

	/**
	 * Pre-existing (hook, priority) pairs registered by 2+ of the Easy-25
	 * classes, reviewed and accepted as intentional. Keyed by "hook:priority".
	 *
	 * Only the `rocket_exclude_js:10` pair (elementor_subscriber vs
	 * syntaxhighlighter_subscriber) is documented today by the
	 * `SubscriberFactory` registry-order comment. This scan additionally
	 * found 4 more pre-existing pairs that were not previously documented
	 * anywhere: they are unrelated to this migration (hook maps are
	 * byte-identical before/after issue #8789 slices 1-4 — only the presence
	 * guard moved, per the Implementation Plan) and are all instances of the
	 * same benign pattern: a WP Rocket "collector" filter (an exclusions or
	 * preload-list array that callbacks *append* to) that several
	 * plugin-compat classes legitimately contribute to. None of them mutate
	 * shared state in a way where registration order matters, unlike the
	 * documented pair. Flagged here for visibility per the AC, not because
	 * they are bugs.
	 *
	 * @var array<string,array<string>>
	 */
	private const ACCEPTED_COLLISIONS = [
		// Documented by SubscriberFactory::get_registry()'s own comment (order
		// preserved intentionally); this scan additionally found pdfembedder
		// also on the same hook + priority, which that comment does not
		// mention.
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
	 * Loads the marker stubs needed by the 3 deviation classes whose
	 * `get_subscribed_events()` still has its own internal guard.
	 *
	 * Deliberately NOT wired into set_up_before_class(): that hook only ever
	 * runs once, in the main (non-isolated) PHPUnit process, even for test
	 * methods annotated `@runInSeparateProcess` below. These stubs declare
	 * real global classes/functions (`Jetpack`, `seopress_get_toggle_option()`,
	 * `the_seo_framework()`) that PHP can never "undeclare" afterwards, so
	 * loading them there would leak into every other test that shares this
	 * suite's single PHP process for the rest of the run — e.g. it would
	 * silently flip PluginResolver::get_active_plugins()'s jetpack/seopress/
	 * the_seo_framework detection to "active" for the remainder of the suite,
	 * breaking Test_PluginCompatSubscribersBehaviorEquivalence's absence
	 * assertions (confirmed locally: this exact leak was caught by a full
	 * --group ThirdParty run before this method was isolated). Called instead
	 * from inside each `@runInSeparateProcess` test method that needs it, so
	 * the pollution is contained to that method's own forked, throwaway
	 * process.
	 */
	private static function load_deviation_marker_stubs(): void {
		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/classes/Jetpack.php';
		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/ThirdParty/Plugins/SEO/SEOPress/fixtures.php';
		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/ThirdParty/Plugins/SEO/TheSEOFramework/fixtures.php';
	}

	/**
	 * Authoritative id => FQCN map for the Easy-25 batch, reconciled against
	 * PluginResolverGatedIds::IDS (the single source of truth for this issue's
	 * gated ids, shared with the baseline equivalence test).
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
			'syntaxhighlighter_subscriber' => SyntaxHighlighter_Subscriber::class,
			'ngg_subscriber'               => NGG_Subscriber::class,
			'autoptimize'                  => Autoptimize::class,
		];
	}

	/**
	 * Sanity-check the enumeration itself: this scan must cover exactly the
	 * 25 ids issue #8789 gates, no more, no less, so a future slice can't
	 * silently drift out of sync with PluginResolverGatedIds::IDS.
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
	 * The AC-required assertion: no WordPress hook is registered by 2+ of the
	 * Easy-25 classes at the same priority, except the reviewed, documented
	 * pairs in self::ACCEPTED_COLLISIONS.
	 *
	 * Isolated (see load_deviation_marker_stubs()'s docblock): capturing
	 * Jetpack/SEOPress/TheSEOFramework's real hook maps requires declaring
	 * real global markers that must not leak into the rest of the suite.
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

					// Keyed by id to dedupe a class registering the same
					// hook+priority twice with itself (e.g. Autoptimize's two
					// admin_notices callbacks) - that's not a cross-class collision.
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
