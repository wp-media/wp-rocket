<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights;

use WP_Rocket\Dependencies\League\Container\Argument\Literal\StringArgument;
use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Admin\RocketInsights\{
	Database\Tables\RocketInsights as RITable,
	Database\Queries\RocketInsights as RIQuery,
	APIHandler\APIClient as RIAPIClient,
	Context\Context,
	Context\SaasContext,
	Jobs\Factory as RIFactory,
	Jobs\Manager as RIManager,
	Managers\Plan,
	Queue\Queue as RIQueue,
	URLLimit\Subscriber as URLLimitSubscriber,
	Settings\Controller as SettingsController,
	Settings\Subscriber as SettingsSubscriber,
	PostListing\Subscriber as PostListingSubscriber,
};
use WP_Rocket\Engine\Common\JobManager\Queue\Queue as JobManagerQueue;

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
		'ri_table',
		'ri_query',
		'ri_api_client',
		'ri_context',
		'ri_saas_context',
		'ri_manager',
		'ri_factory',
		'ri_queue',
		'ri_processor',
		'ri_render',
		'ri_controller',
		'ri_subscriber',
		'ri_rest',
		'ri_global_score',
		'ri_url_limit_subscriber',
		'ri_settings',
		'ri_settings_subscriber',
		'ri_plan',
		'ri_post_listing_subscriber',
		'job_manager_queue',
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
	 * Registers the classes in the container
	 *
	 * @return void
	 */
	public function register(): void {
		// Database layer.
		$this->getContainer()->addShared( 'ri_table', RITable::class );
		$this->getContainer()->add( 'ri_query', RIQuery::class );

		// Context.
		$this->getContainer()->add( 'ri_context', Context::class )
			->addArguments(
				[
					'options',
					'user',
					'ri_query',
					'remote_settings',
				]
			);

		$this->getContainer()->add( 'ri_saas_context', SaasContext::class )
			->addArgument( 'ri_context' );

		$this->getContainer()->add( 'ri_render', Render::class )
			->addArguments(
				[
					new StringArgument( $this->getContainer()->get( 'template_path' ) . '/settings/' ),
					'ri_plan',
					'ri_context',
					'beacon',
					'ri_query',
				]
			);

		// API Client.
		$this->getContainer()->add( 'ri_api_client', RIAPIClient::class )
			->addArgument( 'options' );

		$this->getContainer()->add( 'ri_plan', Plan::class )
			->addArguments(
				[
					'options_api',
					'ri_context',
					'user',
					'user_client',
					'remote_settings_client',
				]
			);

		// Jobs layer.
		$this->getContainer()->add( 'ri_manager', RIManager::class )
			->addArguments(
				[
					'ri_query',
					'ri_saas_context',
					'ri_plan',
				]
			);

		// Global Score layer.
		$this->getContainer()->add( 'ri_global_score', GlobalScore::class )
			->addArguments(
				[
					'ri_query',
				]
			);

		$this->getContainer()->add( 'ri_controller', Controller::class )
			->addArguments(
				[
					'ri_query',
					'ri_manager',
					'ri_context',
					'ri_plan',
					'ri_global_score',
					'user',
					'options',
				]
			);

		$this->getContainer()->addShared( 'ri_factory', RIFactory::class )
			->addArguments(
				[
					'ri_manager',
					'ri_table',
					'ri_api_client',
				]
			);

		// Queue layer.
		$this->getContainer()->add( 'ri_queue', RIQueue::class );
		$this->getContainer()->add( 'job_manager_queue', JobManagerQueue::class );
		$this->getContainer()->add( 'ri_rest', Rest::class )
			->addArguments(
				[
					'ri_query',
					'ri_manager',
					'ri_context',
					'ri_global_score',
					'ri_render',
					'ri_plan',
					'job_processor',
					'job_manager_queue',
				]
			);
		// Subscriber.
		$this->getContainer()->addShared( 'ri_subscriber', Subscriber::class )
			->addArguments(
				[
					'ri_render',
					'ri_controller',
					'ri_rest',
					'ri_queue',
					'ri_context',
					'ri_global_score',
					'options',
					'ri_manager',
					'ri_plan',
					'renewal',
				]
			);

		// URL Limit subscriber.
		$this->getContainer()->addShared( 'ri_url_limit_subscriber', URLLimitSubscriber::class )
			->addArguments(
				[
					'ri_query',
					'user',
					'ri_global_score',
					'ri_context',
				]
			);
		// Settings Subscriber.
		$this->getContainer()->add( 'ri_settings', SettingsController::class )
			->addArguments(
				[
					'user',
					new StringArgument( __DIR__ . '/../../../Engine/License/views' ),
					'ri_context',
				]
			);
		$this->getContainer()->addShared( 'ri_settings_subscriber', SettingsSubscriber::class )
			->addArgument( 'ri_settings' );

		// Post Listing Subscriber.
		$this->getContainer()->addShared( 'ri_post_listing_subscriber', PostListingSubscriber::class )
			->addArguments(
				[
					'ri_render',
					'ri_context',
				]
			);

		// Ensure the table is created.
		$this->getContainer()->get( 'ri_table' );
	}
}
