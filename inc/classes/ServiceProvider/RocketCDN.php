<?php
namespace WP_Rocket\ServiceProvider;

use League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for RocketCDN
 *
 * @since 3.5
 * @author Remy Perona
 */
class RocketCDN extends AbstractServiceProvider {
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
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function register() {
		// RocketCDN API Client.
		$this->getContainer()->add( 'rocketcdn_api_client', 'WP_Rocket\CDN\RocketCDN\APIClient' );
		// RocketCDN CDN options manager.
		$this->getContainer()->add( 'rocketcdn_options_manager', 'WP_Rocket\CDN\RocketCDN\CDNOptionsManager' )
			->withArgument( $this->getContainer()->get( 'options_api' ) )
			->withArgument( $this->getContainer()->get( 'options' ) );
		// RocketCDN Data manager subscriber.
		$this->getContainer()->share( 'rocketcdn_data_manager_subscriber', 'WP_Rocket\Subscriber\CDN\RocketCDN\DataManagerSubscriber' )
			->withArgument( $this->getContainer()->get( 'rocketcdn_api_client' ) )
			->withArgument( $this->getContainer()->get( 'rocketcdn_options_manager' ) );
		// RocketCDN REST API Subscriber.
		$this->getContainer()->share( 'rocketcdn_rest_subscriber', 'WP_Rocket\Subscriber\CDN\RocketCDN\RESTSubscriber' )
			->withArgument( $this->getContainer()->get( 'rocketcdn_options_manager' ) )
			->withArgument( $this->getContainer()->get( 'options' ) );
		// RocketCDN Notices Subscriber.
		$this->getContainer()->share( 'rocketcdn_notices_subscriber', 'WP_Rocket\Subscriber\CDN\RocketCDN\NoticesSubscriber' )
			->withArgument( $this->getContainer()->get( 'rocketcdn_api_client' ) )
			->withArgument( $this->getContainer()->get( 'template_path' ) . '/settings/rocketcdn' );
		// RocketCDN settings page subscriber.
		$this->getContainer()->share( 'rocketcdn_admin_subscriber', 'WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber' )
			->withArgument( $this->getContainer()->get( 'rocketcdn_api_client' ) )
			->withArgument( $this->getContainer()->get( 'options' ) )
			->withArgument( $this->getContainer()->get( 'beacon' ) )
			->withArgument( $this->getContainer()->get( 'template_path' ) . '/settings/rocketcdn' );
	}
}
