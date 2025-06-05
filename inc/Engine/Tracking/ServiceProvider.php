<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Tracking;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WPMedia\Mixpanel\Tracking as MixpanelTracking;

class ServiceProvider extends AbstractServiceProvider {
	/**
	 * Array of services provided by this service provider
	 *
	 * @var array
	 */
	protected $provides = [
		'mixpanel_tracking',
		'tracking',
		'tracking_subscriber',
	];

	/**
	 * Check if the service provider provides a specific service.
	 *
	 * @param string $id The id of the service.
	 *
	 * @return bool
	 */
	public function provides( string $id ): bool {
		return in_array( $id, $this->provides, true );
	}

	/**
	 * Registers the services in the container
	 *
	 * @return void
	 */
	public function register(): void {
		$this->getContainer()->add( 'mixpanel_tracking', MixpanelTracking::class )
			// Mixpanel toke for staging.
			->addArgument( 'ca194771e8caa6fca7ff02896cded17d' );
		$this->getContainer()->add( 'tracking', Tracking::class )
			->addArguments(
				[
					'options',
					'mixpanel_tracking',
				]
			);
		$this->getContainer()->add( 'tracking_subscriber', Subscriber::class )
			->addArgument( 'tracking' );
	}
}
