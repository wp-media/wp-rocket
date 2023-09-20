<?php
namespace WP_Rocket\Engine\CDN\RocketCDN;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for RocketCDN
 *
 * @since 3.5
 */
class ServiceProvider extends AbstractServiceProvider {
	/**
	 * The provides array is a way to let the container
	 * know that a service is provided by this service
	 * provider. Every service that is registered via
	 * this service provider must have an alias added
	 * to this array or it will be ignored.
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
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$options = $this->getContainer()->get( 'options' );
		// RocketCDN API Client.
		$this->getContainer()->add( 'rocketcdn_api_client', APIClient::class );
		// RocketCDN CDN options manager.
		$this->getContainer()->add( 'rocketcdn_options_manager', CDNOptionsManager::class )
			->addArgument( $this->getContainer()->get( 'options_api' ) )
			->addArgument( $options );
		// RocketCDN Data manager subscriber.
		$this->getContainer()->share( 'rocketcdn_data_manager_subscriber', DataManagerSubscriber::class )
			->addArgument( $this->getContainer()->get( 'rocketcdn_api_client' ) )
			->addArgument( $this->getContainer()->get( 'rocketcdn_options_manager' ) )
			->addTag( 'admin_subscriber' );
		// RocketCDN REST API Subscriber.
		$this->getContainer()->share( 'rocketcdn_rest_subscriber', RESTSubscriber::class )
			->addArgument( $this->getContainer()->get( 'rocketcdn_options_manager' ) )
			->addArgument( $options )
			->addTag( 'common_subscriber' );
		// RocketCDN Notices Subscriber.
		$this->getContainer()->share( 'rocketcdn_notices_subscriber', NoticesSubscriber::class )
			->addArgument( $this->getContainer()->get( 'rocketcdn_api_client' ) )
			->addArgument( $this->getContainer()->get( 'beacon' ) )
			->addArgument( __DIR__ . '/views' )
			->addTag( 'admin_subscriber' );
		// RocketCDN settings page subscriber.
		$this->getContainer()->share( 'rocketcdn_admin_subscriber', AdminPageSubscriber::class )
			->addArgument( $this->getContainer()->get( 'rocketcdn_api_client' ) )
			->addArgument( $options )
			->addArgument( $this->getContainer()->get( 'beacon' ) )
			->addArgument( __DIR__ . '/views' )
			->addTag( 'admin_subscriber' );
	}
}
