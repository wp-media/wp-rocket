<?php
namespace WP_Rocket\Engine\CDN\RocketCDN;

use WP_Rocket\AbstractServiceProvider;
use WP_Rocket\Engine\Admin\Beacon\ServiceProvider as BeaconServiceProvider;
/**
 * Service provider for RocketCDN
 *
 * @since 3.5
 */
class ServiceProvider extends AbstractServiceProvider {

	public function get_admin_subscribers(): array
	{
		return [
			$this->generate_container_id('rocketcdn_data_manager_subscriber'),
			$this->generate_container_id('rocketcdn_notices_subscriber'),
			$this->generate_container_id('rocketcdn_admin_subscriber'),
		];
	}

	public function get_common_subscribers(): array
	{
		return [
			$this->generate_container_id('rocketcdn_rest_subscriber')
		];
	}

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$options = $this->get_external( 'options' );
		// RocketCDN API Client.
		$this->add( 'rocketcdn_api_client', APIClient::class );
		// RocketCDN CDN options manager.
		$this->add( 'rocketcdn_options_manager', CDNOptionsManager::class )
			->addArgument( $this->get_external( 'options_api' ) )
			->addArgument( $options );
		// RocketCDN Data manager subscriber.
		$this->share( 'rocketcdn_data_manager_subscriber', DataManagerSubscriber::class )
			->addArgument( $this->get_internal( 'rocketcdn_api_client' ) )
			->addArgument( $this->get_internal( 'rocketcdn_options_manager' ) )
			->addTag( 'admin_subscriber' );
		// RocketCDN REST API Subscriber.
		$this->share( 'rocketcdn_rest_subscriber', RESTSubscriber::class )
			->addArgument( $this->get_internal( 'rocketcdn_options_manager' ) )
			->addArgument( $options )
			->addTag( 'common_subscriber' );
		// RocketCDN Notices Subscriber.
		$this->share( 'rocketcdn_notices_subscriber', NoticesSubscriber::class )
			->addArgument( $this->get_internal( 'rocketcdn_api_client' ) )
			->addArgument( $this->get_external( 'beacon', BeaconServiceProvider::class ) )
			->addArgument( __DIR__ . '/views' )
			->addTag( 'admin_subscriber' );
		// RocketCDN settings page subscriber.
		$this->share( 'rocketcdn_admin_subscriber', AdminPageSubscriber::class )
			->addArgument( $this->get_internal( 'rocketcdn_api_client' ) )
			->addArgument( $options )
			->addArgument( $this->get_external( 'beacon', BeaconServiceProvider::class ) )
			->addArgument( __DIR__ . '/views' )
			->addTag( 'admin_subscriber' );
	}
}
