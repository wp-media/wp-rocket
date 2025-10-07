<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring;

use WP_Rocket\Dependencies\League\Container\Argument\Literal\StringArgument;
use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\{
	Database\Tables\PerformanceMonitoring as PMTable,
	Database\Queries\PerformanceMonitoring as PMQuery,
	APIHandler\APIClient as PMAPIClient,
	Context\PerformanceMonitoringContext,
	Jobs\Factory as PMFactory,
	Jobs\Manager as PMManager,
	Managers\Plan,
	Queue\Queue as PMQueue,
	AJAX\Controller as AjaxController,
	URLLimit\Subscriber as URLLimitSubscriber,
	Settings\Controller as SettingsController,
	Settings\Subscriber as SettingsSubscriber,
};

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
		'pm_table',
		'pm_query',
		'pm_api_client',
		'pm_context',
		'pm_manager',
		'pm_factory',
		'pm_queue',
		'pm_processor',
		'pm_render',
		'pm_controller',
		'pm_subscriber',
		'pm_ajax_controller',
		'pm_global_score',
		'pm_url_limit_subscriber',
		'rocket_insights_settings',
		'pm_settings_subscriber',
		'pm_plan',
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
		$this->getContainer()->addShared( 'pm_table', PMTable::class );
		$this->getContainer()->add( 'pm_query', PMQuery::class );

		// Context.
		$this->getContainer()->add( 'pm_context', PerformanceMonitoringContext::class )
			->addArguments(
				[
					'options',
					'user',
					'pm_query',
				]
			);

		$this->getContainer()->add( 'pm_render', Render::class )
			->addArguments(
				[
					new StringArgument( $this->getContainer()->get( 'template_path' ) . '/settings/' ),
					'pm_plan',
					'pm_context',
					'beacon',
				]
				);

		// API Client.
		$this->getContainer()->add( 'pm_api_client', PMAPIClient::class )
			->addArgument( 'options' );

		$this->getContainer()->add( 'pm_plan', Plan::class )
			->addArguments(
				[
					'options_api',
					'pm_context',
					'user',
					'user_client',
				]
			);

		// Jobs layer.
		$this->getContainer()->add( 'pm_manager', PMManager::class )
			->addArguments(
				[
					'pm_query',
					'pm_context',
					'pm_plan',
				]
			);

		// Global Score layer.
		$this->getContainer()->add( 'pm_global_score', GlobalScore::class )
			->addArguments(
				[
					'pm_query',
				]
			);

		$this->getContainer()->add( 'pm_controller', Controller::class )
			->addArguments(
				[
					'pm_query',
					'pm_manager',
					'pm_context',
					'pm_plan',
					'pm_global_score',
					'user',
					'options',
				]
			);

		$this->getContainer()->addShared( 'pm_factory', PMFactory::class )
			->addArguments(
				[
					'pm_manager',
					'pm_table',
					'pm_api_client',
				]
			);

		// Queue layer.
		$this->getContainer()->add( 'pm_queue', PMQueue::class );
		$this->getContainer()->add( 'pm_ajax_controller', AjaxController::class )
			->addArguments(
				[
					'pm_query',
					'pm_manager',
					'pm_context',
					'pm_global_score',
					'pm_render',
					'pm_plan',
				]
			);
		// Subscriber.
		$this->getContainer()->addShared( 'pm_subscriber', Subscriber::class )
			->addArguments(
				[
					'pm_render',
					'pm_controller',
					'pm_ajax_controller',
					'pm_queue',
					'pm_context',
					'pm_global_score',
					'options',
					'pm_manager',
					'pm_plan',
				]
			);

		// URL Limit subscriber.
		$this->getContainer()->addShared( 'pm_url_limit_subscriber', URLLimitSubscriber::class )
			->addArguments(
				[
					'pm_query',
					'user',
					'pm_global_score',
					'pm_context',
				]
			);
		// Settings Subscriber.
		$this->getContainer()->add( 'rocket_insights_settings', SettingsController::class )
			->addArguments(
				[
					'user',
					new StringArgument( __DIR__ . '/../../../Engine/License/views' ),
					'pm_context',
				]
			);
		$this->getContainer()->addShared( 'pm_settings_subscriber', SettingsSubscriber::class )
			->addArgument( 'rocket_insights_settings' );

		// Ensure the table is created.
		$this->getContainer()->get( 'pm_table' );
	}
}
