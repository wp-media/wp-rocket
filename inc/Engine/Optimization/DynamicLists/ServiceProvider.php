<?php

namespace WP_Rocket\Engine\Optimization\DynamicLists;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Optimization\DynamicLists\DefaultLists\APIClient as DefaultListsAPIClient;
use WP_Rocket\Engine\Optimization\DynamicLists\DefaultLists\DataManager as DefaultListsDataManager;
use WP_Rocket\Engine\Optimization\DynamicLists\DelayJSLists\APIClient as DelayJSListsAPIClient;
use WP_Rocket\Engine\Optimization\DynamicLists\DelayJSLists\DataManager as DelayJSListsDataManager;

/**
 * Service provider for the WP Rocket DynamicLists
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
		'dynamic_lists_defaultlists_data_manager',
		'dynamic_lists_defaultlists_api_client',
		'dynamic_lists_delayjslists_data_manager',
		'dynamic_lists_delayjslists_api_client',
		'dynamic_lists',
		'dynamic_lists_subscriber',
	];

	/**
	 * Registers the option array in the container
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->add( 'dynamic_lists_defaultlists_data_manager', DefaultListsDataManager::class );
		$this->getContainer()->add( 'dynamic_lists_defaultlists_api_client', DefaultListsAPIClient::class )
			->addArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->add( 'dynamic_lists_delayjslists_data_manager', DelayJSListsDataManager::class );
		$this->getContainer()->add( 'dynamic_lists_delayjslists_api_client', DelayJSListsAPIClient::class )
			->addArgument( $this->getContainer()->get( 'options' ) );

		$providers = [
			'defaultlists' =>
				(object) [
					'api_client'   => $this->getContainer()->get( 'dynamic_lists_defaultlists_api_client' ),
					'data_manager' => $this->getContainer()->get( 'dynamic_lists_defaultlists_data_manager' ),
					'title'        => __( 'Default Lists', 'rocket' ),
				],
			'delayjslists' =>
				(object) [
					'api_client'   => $this->getContainer()->get( 'dynamic_lists_delayjslists_api_client' ),
					'data_manager' => $this->getContainer()->get( 'dynamic_lists_delayjslists_data_manager' ),
					'title'        => __( 'Delay JavaScript Execution Exclusion Lists', 'rocket' ),
				],
		];

		$this->getContainer()->add( 'dynamic_lists', DynamicLists::class )
			->addArgument( $providers )
			->addArgument( $this->getContainer()->get( 'user' ) )
			->addArgument( $this->getContainer()->get( 'template_path' ) )
			->addArgument( $this->getContainer()->get( 'beacon' ) );

		$this->getContainer()->share( 'dynamic_lists_subscriber', Subscriber::class )
			->addArgument( $this->getContainer()->get( 'dynamic_lists' ) );
	}
}
