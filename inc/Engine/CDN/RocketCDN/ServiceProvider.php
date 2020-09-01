<?php
namespace WP_Rocket\Engine\CDN\RocketCDN;

use WP_Rocket\Engine\Container\ServiceProvider\AbstractServiceProvider;

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
	 * Registers the RocketCDN classes in the container
	 *
	 * @since 3.5
	 *
	 * @return void
	 */
	public function register() {
		$options = $this->getContainer()->get( 'options' );
		// RocketCDN API Client.
		$this->getContainer()->add( 'rocketcdn_api_client', 'WP_Rocket\Engine\CDN\RocketCDN\APIClient' );
		// RocketCDN CDN options manager.
		$this->getContainer()->add( 'rocketcdn_options_manager', 'WP_Rocket\Engine\CDN\RocketCDN\CDNOptionsManager' )
			->withArgument( $this->getContainer()->get( 'options_api' ) )
			->withArgument( $options );
		// RocketCDN Data manager subscriber.
		$this->getContainer()->share( 'rocketcdn_data_manager_subscriber', 'WP_Rocket\Engine\CDN\RocketCDN\DataManagerSubscriber' )
			->withArgument( $this->getContainer()->get( 'rocketcdn_api_client' ) )
			->withArgument( $this->getContainer()->get( 'rocketcdn_options_manager' ) );
		// RocketCDN REST API Subscriber.
		$this->getContainer()->share( 'rocketcdn_rest_subscriber', 'WP_Rocket\Engine\CDN\RocketCDN\RESTSubscriber' )
			->withArgument( $this->getContainer()->get( 'rocketcdn_options_manager' ) )
			->withArgument( $options );
		// RocketCDN Notices Subscriber.
		$this->getContainer()->share( 'rocketcdn_notices_subscriber', 'WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber' )
			->withArgument( $this->getContainer()->get( 'rocketcdn_api_client' ) )
			->withArgument( __DIR__ . '/views' );
		// RocketCDN settings page subscriber.
		$this->getContainer()->share( 'rocketcdn_admin_subscriber', 'WP_Rocket\Engine\CDN\RocketCDN\AdminPageSubscriber' )
			->withArgument( $this->getContainer()->get( 'rocketcdn_api_client' ) )
			->withArgument( $options )
			->withArgument( $this->getContainer()->get( 'beacon' ) )
			->withArgument( __DIR__ . '/views' );
	}
}
