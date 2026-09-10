<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Fleet;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WPMedia\FleetBridge\WordPress\WpdbNonceStore;

/**
 * Hooks the Fleet route into WordPress.
 *
 * Nothing here decides whether Fleet is allowed. The route is registered
 * unconditionally and refuses on its own, because the alternative — not
 * registering it when consent is off — makes a site with Fleet switched off
 * answer 404 where a site that has simply never polled answers 401, and the
 * difference tells an outsider which licences have opted in.
 *
 * @since 3.23.4
 */
class Subscriber implements Subscriber_Interface {
	/**
	 * Housekeeping event: deletes spent command identifiers that have expired.
	 *
	 * @var string
	 */
	const PURGE_EVENT = 'rocket_fleet_purge_nonces';

	/**
	 * The route.
	 *
	 * @var Route
	 */
	private $route;

	/**
	 * Instantiate the class.
	 *
	 * @param Route $route The route.
	 */
	public function __construct( Route $route ) {
		$this->route = $route;
	}

	/**
	 * Events this subscriber listens to.
	 *
	 * @since 3.23.4
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'rest_api_init'       => [ 'register_routes' ],
			'init'                => 'schedule_purge',
			'rocket_deactivation' => 'unschedule_purge',
			self::PURGE_EVENT     => 'purge_nonces',
		];
	}

	/**
	 * Register the route.
	 *
	 * @since 3.23.4
	 *
	 * @return void
	 */
	public function register_routes(): void {
		$this->route->register_routes();
	}

	/**
	 * Schedule the identifier housekeeping, once.
	 *
	 * Every request from Fleet carries two one-time identifiers — one on the
	 * command, one on the consent grant — and the site records both so neither
	 * can be replayed. Those records are rows in the options table, two per
	 * honoured request, and without this they accumulate for the life of the
	 * install.
	 *
	 * Scheduled unconditionally rather than only when Fleet is allowed: a site
	 * that switches Fleet off still has whatever rows it already wrote, and a
	 * site that has never used Fleet purges nothing at a cost of one query a
	 * day.
	 *
	 * @since 3.23.4
	 *
	 * @return void
	 */
	public function schedule_purge(): void {
		if ( wp_next_scheduled( self::PURGE_EVENT ) ) {
			return;
		}

		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::PURGE_EVENT );
	}

	/**
	 * Stop the housekeeping when the plugin is deactivated.
	 *
	 * @since 3.23.4
	 *
	 * @return void
	 */
	public function unschedule_purge(): void {
		wp_clear_scheduled_hook( self::PURGE_EVENT );
	}

	/**
	 * Delete spent identifiers that have expired anyway.
	 *
	 * Housekeeping only. An expired command is refused on its own expiry claim
	 * whether or not its row is still here, so nothing about this is load
	 * bearing for security — running it late, or not at all, costs table size
	 * and never permission.
	 *
	 * @since 3.23.4
	 *
	 * @return void
	 */
	public function purge_nonces(): void {
		WpdbNonceStore::purge();
	}
}
