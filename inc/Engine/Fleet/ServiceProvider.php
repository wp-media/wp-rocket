<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Fleet;

use WPMedia\FleetBridge\Bridge;
use WPMedia\FleetBridge\Config;
use WPMedia\FleetBridge\WordPress\HttpKeySets;
use WPMedia\FleetBridge\WordPress\SystemClock;
use WPMedia\FleetBridge\WordPress\WpdbNonceStore;
use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Wires the Fleet route.
 *
 * Verification comes from `wp-media/fleet-bridge` rather than living here, per
 * wp-media/fleet#55: the two-credential protocol is security-critical, and
 * assertion verification written twice is assertion verification wrong once.
 * WP Rocket is the first plugin to install it and will not be the last.
 *
 * What stays here is the part that is genuinely ours: where trust comes from,
 * and what a command is allowed to do once it has been believed.
 *
 * The two abilities are pulled from the container rather than constructed, so
 * the route calls the very same objects the MCP surface does. Building a second
 * `SetOption` would be a second place for the allowlist and the sanitisation
 * to live.
 *
 * @since 3.23.4
 */
class ServiceProvider extends AbstractServiceProvider {
	/**
	 * Services provided by this service provider.
	 *
	 * @var array
	 */
	protected $provides = [
		'fleet_trust_store',
		'fleet_bridge',
		'fleet_route',
		'fleet_subscriber',
	];

	/**
	 * Does this provider provide a service.
	 *
	 * @param string $id Service id.
	 *
	 * @return bool
	 */
	public function provides( string $id ): bool {
		return in_array( $id, $this->provides, true );
	}

	/**
	 * Register the services.
	 *
	 * @return void
	 */
	public function register(): void {
		$this->getContainer()->addShared( 'fleet_trust_store', TrustStore::class )
			->addArgument( 'remote_settings_client' );

		$this->getContainer()->addShared(
			'fleet_bridge',
			function () {
				return new Bridge(
					$this->getContainer()->get( 'fleet_trust_store' ),
					new HttpKeySets( [ Route::class, 'log' ] ),
					new WpdbNonceStore(),
					// Port free: `home_url()` may carry one, and wp-rocket.me
					// stores the domain without it. Both sides derive the
					// subject from this string, so a port in one and not the
					// other is a refusal that looks like a signature problem.
					new Config( (string) wp_parse_url( home_url(), PHP_URL_HOST ) ),
					new SystemClock()
				);
			}
		);

		$this->getContainer()->addShared( 'fleet_route', Route::class )
			->addArguments(
				[
					'fleet_bridge',
					'abilities_get_options',
					'abilities_set_option',
					'abilities_allowed_options',
				]
			);

		$this->getContainer()->addShared( 'fleet_subscriber', Subscriber::class )
			->addArgument( 'fleet_route' );
	}
}
