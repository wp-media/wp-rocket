<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Fleet;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WPMedia\FleetBridge\WordPress\WpdbNonceStore;

/**
 * Hooks the Fleet route into WordPress.
 *
 * Nothing here decides whether Fleet is allowed: the route is registered
 * unconditionally and refuses on its own. Registering it only when consent is
 * on would make 404 against 401 leak which licences have opted in.
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
	 * Each honoured request records two one-time identifiers, one for the
	 * command and one for the consent grant, as rows in the options table.
	 * Without this they accumulate for the life of the install.
	 *
	 * Scheduled unconditionally: a site that switches Fleet off still holds
	 * the rows it wrote, and one that never used it purges nothing.
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
	 * either way, so running this late costs table size and never permission.
	 *
	 * @since 3.23.4
	 *
	 * @return void
	 */
	public function purge_nonces(): void {
		WpdbNonceStore::purge();
	}
}
