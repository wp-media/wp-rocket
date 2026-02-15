<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN\RocketCDN;

use WP_Rocket\Dependencies\League\Container\Argument\Literal\StringArgument;
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
		// RocketCDN API Client.
		$this->getContainer()->add( 'rocketcdn_api_client', APIClient::class );
		// RocketCDN CDN options manager.
		$this->getContainer()->add( 'rocketcdn_options_manager', CDNOptionsManager::class )
			->addArguments(
				[
					'options_api',
					'options',
				]
			);
		// RocketCDN Data manager subscriber.
		$this->getContainer()->addShared( 'rocketcdn_data_manager_subscriber', DataManagerSubscriber::class )
			->addArguments(
				[
					'rocketcdn_api_client',
					'rocketcdn_options_manager',
					'options',
					'options_api',
				]
			);
		// RocketCDN REST API Subscriber.
		$this->getContainer()->addShared( 'rocketcdn_rest_subscriber', RESTSubscriber::class )
			->addArguments(
				[
					'rocketcdn_options_manager',
					'options',
				]
			);
		// RocketCDN Notices Subscriber.
		$this->getContainer()->addShared( 'rocketcdn_notices_subscriber', NoticesSubscriber::class )
			->addArguments(
				[
					'rocketcdn_api_client',
					'beacon',
					new StringArgument( __DIR__ . '/views' ),
				]
			);
		// RocketCDN settings page subscriber.
		$this->getContainer()->addShared( 'rocketcdn_admin_subscriber', AdminPageSubscriber::class )
			->addArguments(
				[
					'rocketcdn_api_client',
					'options',
					'beacon',
					new StringArgument( __DIR__ . '/views' ),
				]
			);
	}
}
