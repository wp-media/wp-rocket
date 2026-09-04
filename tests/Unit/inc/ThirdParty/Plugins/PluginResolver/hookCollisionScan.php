<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\PluginResolver;

use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Optimus_Subscriber;
use WP_Rocket\Tests\Fixtures\classes\PluginResolverGatedIds;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare;
use WP_Rocket\ThirdParty\Plugins\ContactForm7;
use WP_Rocket\ThirdParty\Plugins\Optimization\Hummingbird;
use WP_Rocket\ThirdParty\Plugins\Optimization\RapidLoad;
use WP_Rocket\ThirdParty\Plugins\RevolutionSlider;
use WP_Rocket\ThirdParty\Plugins\SEO\AllInOneSEOPack;

/**
 * Verifies that none of the 7 gated plugin-compat classes accidentally clash
 * on the same WordPress hook + priority.
 *
 * `get_subscribed_events()` is a static method with no WordPress/container
 * dependency, so it is called directly (`Class::get_subscribed_events()`)
 * with no instantiation involved. 4 of the 7 classes still guard their hook
 * map with a "target plugin present" check *inside* `get_subscribed_events()`
 * itself, so only these 4 need their marker constant defined to reveal their
 * real (maximal) hook set:
 * - RevolutionSlider: needs `RS_REVISION` defined at >= '6.5.5'.
 * - Optimus_Subscriber: needs `OPTIMUS_FILE` defined.
 * - RapidLoad: needs `UUCSS_VERSION` defined.
 * - AllInOneSEOPack: needs `AIOSEOP_VERSION` defined.
 *
 * These are real PHP constants that, once defined, can never be undefined,
 * so the test methods that define them run `@runInSeparateProcess` to avoid
 * leaking into the rest of the Unit suite.
 *
 * ContactForm7, Cloudflare and Hummingbird return their hook map
 * unconditionally, so they need no marker stubbing at all.
 *
 * @group ThirdParty
 * @group Plugins
 */
class Test_HookCollisionScan extends TestCase {

	/**
	 * (hook, priority) pairs registered by 2+ of the 7 classes, reviewed and
	 * accepted as intentional. Keyed by "hook:priority".
	 *
	 * @var array<string,array<string>>
	 */
	private const ACCEPTED_COLLISIONS = [
		// admin_notices is a WordPress action, not a value-collecting filter:
		// multiple independent callbacks rendering their own notice at the
		// same priority is the normal, benign WordPress pattern and neither
		// callback depends on the other's registration order.
		'admin_notices:10' => [
			'cloudflare_plugin_subscriber',
			'hummingbird_subscriber',
		],
	];

	/**
	 * Defines the marker constants needed by the 4 deviation classes whose
	 * `get_subscribed_events()` still has its own internal "target plugin
	 * present" guard, so their real hook map is revealed.
	 *
	 * Only called from inside `@runInSeparateProcess` test methods: these are
	 * real PHP constants that, once defined, can never be undefined, so they
	 * must stay contained to their own forked, throwaway process rather than
	 * leaking into the rest of the Unit suite.
	 */
	private static function load_deviation_marker_stubs(): void {
		define( 'RS_REVISION', '6.5.5' );
		define( 'OPTIMUS_FILE', '/plugins/optimus/optimus.php' );
		define( 'UUCSS_VERSION', '1.0' );
		define( 'AIOSEOP_VERSION', '3.0' );
	}

	/**
	 * Authoritative id => FQCN map for the 7 gated ids, reconciled against
	 * PluginResolverGatedIds::IDS (the single source of truth for the gated
	 * ids, shared with the baseline equivalence test).
	 *
	 * @return array<string,string>
	 */
	private function gated_classes(): array {
		return [
			'revolution_slider_subscriber' => RevolutionSlider::class,
			'optimus_webp_subscriber'      => Optimus_Subscriber::class,
			'rapidload'                    => RapidLoad::class,
			'all_in_one_seo_pack'          => AllInOneSEOPack::class,
			'contactform7'                 => ContactForm7::class,
			'cloudflare_plugin_subscriber' => Cloudflare::class,
			'hummingbird_subscriber'       => Hummingbird::class,
		];
	}

	/**
	 * Sanity-check the enumeration itself: this scan must cover exactly the
	 * ids gated behind PluginCompatibilityInterface, no more, no less, so it
	 * can't silently drift out of sync with PluginResolverGatedIds::IDS.
	 */
	public function testShouldEnumerateExactlyTheGatedIds() {
		$ids = array_keys( $this->gated_classes() );

		sort( $ids );
		$expected = PluginResolverGatedIds::IDS;
		sort( $expected );

		$this->assertSame( $expected, $ids, 'The collision scan must cover exactly the gated ids, no more, no less.' );
	}

	/**
	 * The core assertion: no WordPress hook is registered by 2+ of the 7
	 * classes at the same priority, except the reviewed, documented pair in
	 * self::ACCEPTED_COLLISIONS.
	 *
	 * Isolated (see load_deviation_marker_stubs()'s docblock): defining the
	 * deviation classes' marker constants declares real, permanent global
	 * constants that must not leak into the rest of the suite.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldNotCollideOnHookAndPriorityExceptAcceptedPairs() {
		self::load_deviation_marker_stubs();

		$hook_priority_to_ids = [];

		foreach ( $this->gated_classes() as $id => $class ) {
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
			"Unexpected hook/priority collision(s) among the gated batch (format 'hook:priority registered by: id1, id2'):\n" . implode( "\n", $unexpected )
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
