<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN\RocketCDN;

use WP_Rocket\Dependencies\League\Container\Argument\Literal\StringArgument;
use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\CDN\RocketCDN\APIHandler\CheckStatusAPIClient;
use WP_Rocket\Engine\CDN\RocketCDN\APIHandler\CreateAPIClient;
use WP_Rocket\Engine\CDN\RocketCDN\APIHandler\WebsiteSearch;
use WP_Rocket\Engine\CDN\RocketCDN\Database\Queries\RocketCDN as RocketCDNQuery;
use WP_Rocket\Engine\CDN\RocketCDN\Database\Tables\RocketCDN as RocketCDNTable;

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
		'rocketcdn_table',
		'rocketcdn_query',
		'rocketcdn_api_client',
		'rocketcdn_options_manager',
		'rocketcdn_data_manager_subscriber',
		'rocketcdn_frontend_subscriber',
		'rocketcdn_rest',
		'rocketcdn_rest_subscriber',
		'rocketcdn_admin_subscriber',
		'rocketcdn_notices_subscriber',
		'rocketcdn_subscription_controller',
		'rocketcdn_create_api_client',
		'rocketcdn_queue',
		'rocketcdn_check_status_api_client',
		'rocketcdn_website_search_api_client',
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
		// RocketCDN database layer.
		$this->getContainer()->addShared( 'rocketcdn_table', RocketCDNTable::class );
		$this->getContainer()->add( 'rocketcdn_query', RocketCDNQuery::class );

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
					'user_client',
					'rocketcdn_subscription_controller',
				]
			);
		// RocketCDN Frontend subscriber.
		$this->getContainer()->addShared( 'rocketcdn_frontend_subscriber', FrontendSubscriber::class )
			->addArguments(
				[
					'cdn_context',
					'rocketcdn_subscription_controller',
				]
			);
		// RocketCDN REST API pages controller.
		$this->getContainer()->add( 'rocketcdn_rest', Rest::class )
			->addArguments(
				[
					'rocketcdn_query',
					'options',
					'options_api',
					'cdn_render_controller',
					'cdn_context',
					'rocketcdn_subscription_controller',
				]
			);
		// RocketCDN REST API Subscriber.
		$this->getContainer()->addShared( 'rocketcdn_rest_subscriber', RESTSubscriber::class )
			->addArguments(
				[
					'rocketcdn_options_manager',
					'options',
					'rocketcdn_rest',
					'rocketcdn_subscription_controller',
				]
			);
		// RocketCDN Notices Subscriber.
		$this->getContainer()->addShared( 'rocketcdn_notices_subscriber', NoticesSubscriber::class )
			->addArguments(
				[
					'rocketcdn_api_client',
					'beacon',
					'user_client',
					'tracking',
					new StringArgument( __DIR__ . '/views' ),
					'options',
					'rocketcdn_subscription_controller',
				]
			);

		$this->getContainer()->add( 'rocketcdn_queue', Queue::class );
		$this->getContainer()->add( 'rocketcdn_create_api_client', CreateAPIClient::class )->addArgument( 'user' );
		$this->getContainer()->add( 'rocketcdn_check_status_api_client', CheckStatusAPIClient::class );
		$this->getContainer()->add( 'rocketcdn_website_search_api_client', WebsiteSearch::class );
		$this->getContainer()->add( 'rocketcdn_subscription_controller', SubscriptionController::class )
			->addArguments(
				[
					'rocketcdn_api_client',
					'rocketcdn_create_api_client',
					'rocketcdn_options_manager',
					'rocketcdn_queue',
					'rocketcdn_check_status_api_client',
					'user',
					'rocketcdn_website_search_api_client',
				]
				);

		// RocketCDN settings page subscriber.
		$this->getContainer()->addShared( 'rocketcdn_admin_subscriber', AdminPageSubscriber::class )
			->addArguments(
				[
					'rocketcdn_api_client',
					'user_client',
					new StringArgument( __DIR__ . '/views' ),
				]
			);

		// Ensure the table is created and refreshed when needed.
		$this->getContainer()->get( 'rocketcdn_table' );
	}
}
