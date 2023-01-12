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

	public function declare()
	{
		// RocketCDN API Client.
		$this->register_service('rocketcdn_api_client', function ($id) {
			$this->add( $id, APIClient::class );
		});

		// RocketCDN CDN options manager.
		$this->register_service('rocketcdn_options_manager', function ($id) {
			$this->add( $id, CDNOptionsManager::class )
				->addArgument( $this->get_external( 'options_api' ) )
				->addArgument( $this->get_external('options') );
		});

		// RocketCDN Data manager subscriber.
		$this->register_service('rocketcdn_data_manager_subscriber', function ($id) {
			$this->share( $id, DataManagerSubscriber::class )
				->addArgument( $this->get_internal( 'rocketcdn_api_client' ) )
				->addArgument( $this->get_internal( 'rocketcdn_options_manager' ) )
				->addTag( 'admin_subscriber' );
		});

		// RocketCDN REST API Subscriber.
		$this->register_service('rocketcdn_rest_subscriber', function ($id) {
			$this->share( $id, RESTSubscriber::class )
				->addArgument( $this->get_internal( 'rocketcdn_options_manager' ) )
				->addArgument( $this->get_external('options') )
				->addTag( 'common_subscriber' );
		});

		// RocketCDN REST API Subscriber.
		$this->register_service('rocketcdn_notices_subscriber', function ($id) {
			// RocketCDN Notices Subscriber.
			$this->share( $id, NoticesSubscriber::class )
				->addArgument( $this->get_internal( 'rocketcdn_api_client' ) )
				->addArgument( $this->get_external( 'beacon', BeaconServiceProvider::class ) )
				->addArgument( __DIR__ . '/views' )
				->addTag( 'admin_subscriber' );
		});

		// RocketCDN REST API Subscriber.
		$this->register_service('rocketcdn_admin_subscriber', function ($id) {
			// RocketCDN settings page subscriber.
			$this->share( $id, AdminPageSubscriber::class )
				->addArgument( $this->get_internal( 'rocketcdn_api_client' ) )
				->addArgument( $this->get_external('options') )
				->addArgument( $this->get_external( 'beacon', BeaconServiceProvider::class ) )
				->addArgument( __DIR__ . '/views' )
				->addTag( 'admin_subscriber' );
		});
	}
}
