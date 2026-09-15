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
 * wp-media/fleet#55. What stays here is where trust comes from and what a
 * command may do once believed.
 *
 * The abilities are pulled from the container rather than constructed, so the
 * route calls the same objects the MCP surface does and the allowlist and
 * sanitisation keep one home.
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
					// Port free: `home_url()` may carry one, wp-rocket.me stores
					// the domain without it, and both sides derive the subject
					// from this string.
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
