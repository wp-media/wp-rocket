<?php
namespace WP_Rocket\Engine\CDN\RocketCDN;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for RocketCDN
 */
class ServiceProvider extends AbstractServiceProvider {
	/**
	 * Array of services provided by this service provider
	 *
	 * @var array
	 */
	protected $provides = [
		'rocketcdn_api_client',
		'rocketcdn_options_manager',
		'rocketcdn_data_manager_subscriber',
		'rocketcdn_rest_subscriber',
		'rocketcdn_admin_subscriber',
		'rocketcdn_notices_subscriber',
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
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register(): void {
		$options = $this->getContainer()->get( 'options' );
		// RocketCDN API Client.
		$this->getContainer()->add( 'rocketcdn_api_client', APIClient::class );
		// RocketCDN CDN options manager.
		$this->getContainer()->add( 'rocketcdn_options_manager', CDNOptionsManager::class )
			->addArgument( $this->getContainer()->get( 'options_api' ) )
			->addArgument( $options );
		// RocketCDN Data manager subscriber.
		$this->getContainer()->addShared( 'rocketcdn_data_manager_subscriber', DataManagerSubscriber::class )
			->addArgument( $this->getContainer()->get( 'rocketcdn_api_client' ) )
			->addArgument( $this->getContainer()->get( 'rocketcdn_options_manager' ) );
		// RocketCDN REST API Subscriber.
		$this->getContainer()->addShared( 'rocketcdn_rest_subscriber', RESTSubscriber::class )
			->addArgument( $this->getContainer()->get( 'rocketcdn_options_manager' ) )
			->addArgument( $options );
		// RocketCDN Notices Subscriber.
		$this->getContainer()->addShared( 'rocketcdn_notices_subscriber', NoticesSubscriber::class )
			->addArgument( $this->getContainer()->get( 'rocketcdn_api_client' ) )
			->addArgument( $this->getContainer()->get( 'beacon' ) )
			->addArgument( __DIR__ . '/views' );
		// RocketCDN settings page subscriber.
		$this->getContainer()->addShared( 'rocketcdn_admin_subscriber', AdminPageSubscriber::class )
			->addArgument( $this->getContainer()->get( 'rocketcdn_api_client' ) )
			->addArgument( $options )
			->addArgument( $this->getContainer()->get( 'beacon' ) )
			->addArgument( __DIR__ . '/views' );
	}
}
