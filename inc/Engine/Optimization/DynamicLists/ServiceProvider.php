<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\DynamicLists;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Optimization\DynamicLists\DefaultLists\{
	APIClient as DefaultListsAPIClient,
	DataManager as DefaultListsDataManager
};
use WP_Rocket\Engine\Optimization\DynamicLists\DelayJSLists\{
	APIClient as DelayJSListsAPIClient,
	DataManager as DelayJSListsDataManager
};
use WP_Rocket\Engine\Optimization\DynamicLists\IncompatiblePluginsLists\{
	APIClient as IncompatiblePluginsListsAPIClient,
	DataManager as IncompatiblePluginsListsDataManager
};

/**
 * Service provider for the WP Rocket DynamicLists
 */
class ServiceProvider extends AbstractServiceProvider {
	/**
	 * Array of services provided by this service provider
	 *
	 * @var array
	 */
	protected $provides = [
		'dynamic_lists_defaultlists_data_manager',
		'dynamic_lists_defaultlists_api_client',
		'dynamic_lists_delayjslists_data_manager',
		'dynamic_lists_delayjslists_api_client',
		'dynamic_lists_incompatible_plugins_lists_data_manager',
		'dynamic_lists_incompatible_plugins_lists_api_client',
		'dynamic_lists',
		'dynamic_lists_subscriber',
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
	 * Registers the option array in the container
	 *
	 * @return void
	 */
	public function register(): void {
		$this->getContainer()->add( 'dynamic_lists_defaultlists_data_manager', DefaultListsDataManager::class );
		$this->getContainer()->add( 'dynamic_lists_defaultlists_api_client', DefaultListsAPIClient::class )
			->addArgument( 'options' );
		$this->getContainer()->add( 'dynamic_lists_delayjslists_data_manager', DelayJSListsDataManager::class );
		$this->getContainer()->add( 'dynamic_lists_delayjslists_api_client', DelayJSListsAPIClient::class )
			->addArgument( 'options' );
		$this->getContainer()->add( 'dynamic_lists_incompatible_plugins_lists_data_manager', IncompatiblePluginsListsDataManager::class )
			->addArgument( 'options' );
		$this->getContainer()->add( 'dynamic_lists_incompatible_plugins_lists_api_client', IncompatiblePluginsListsAPIClient::class )
			->addArgument( 'options' );

		$providers = [
			'defaultlists'         =>
				(object) [
					'api_client'   => $this->getContainer()->get( 'dynamic_lists_defaultlists_api_client' ),
					'data_manager' => $this->getContainer()->get( 'dynamic_lists_defaultlists_data_manager' ),
				],
			'delayjslists'         =>
				(object) [
					'api_client'   => $this->getContainer()->get( 'dynamic_lists_delayjslists_api_client' ),
					'data_manager' => $this->getContainer()->get( 'dynamic_lists_delayjslists_data_manager' ),
				],
			'incompatible_plugins' =>
				(object) [
					'api_client'   => $this->getContainer()->get( 'dynamic_lists_incompatible_plugins_lists_api_client' ),
					'data_manager' => $this->getContainer()->get( 'dynamic_lists_incompatible_plugins_lists_data_manager' ),
					'clear_cache'  => false,
				],
		];

		$this->getContainer()->add( 'dynamic_lists', DynamicLists::class )
			->addArguments(
				[
					$providers,
					'user',
					'template_path',
					'beacon',
				]
			);
		$this->getContainer()->addShared( 'dynamic_lists_subscriber', Subscriber::class )
			->addArgument( 'dynamic_lists' );
	}
}
