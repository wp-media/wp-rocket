<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Tracking;

use WP_Rocket\Dependencies\League\Container\Argument\Literal\StringArgument;
use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WPMedia\Mixpanel\Optin;
use WPMedia\Mixpanel\TrackingPlugin as MixpanelTracking;

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
		$this->getContainer()->add( 'mixpanel_optin', Optin::class )
			->addArguments(
				[
					'rocket',
					'rocket_manage_options',
				]
			);
		$this->getContainer()->add( 'mixpanel_tracking', MixpanelTracking::class )
			->addArguments(
				[
					'517e881edc2636e99a2ecf013d8134d3',
					'WP Rocket ' . rocket_get_constant( 'WP_ROCKET_VERSION', '' ),
					'WP Media',
					'WP Rocket',
				]
			);
		$this->getContainer()->add( 'tracking', Tracking::class )
			->addArguments(
				[
					'options',
					'mixpanel_optin',
					'mixpanel_tracking',
					new StringArgument( $this->getContainer()->get( 'template_path' ) . '/settings/sections/' ),
				]
			);
		$this->getContainer()->add( 'tracking_subscriber', Subscriber::class )
			->addArgument( 'tracking' );
	}
}
