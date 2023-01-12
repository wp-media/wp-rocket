<?php

namespace WP_Rocket\Engine\Optimization\DynamicLists;

use WP_Rocket\AbstractServiceProvider;

/**
 * Service provider for the WP Rocket DynamicLists
 */
class ServiceProvider extends AbstractServiceProvider {

	public function get_common_subscribers(): array
	{
		return [
			$this->generate_container_id('dynamic_lists_subscriber')
		];
	}

	public function declare()
	{
		$this->register_service('dynamic_lists_data_manager', function ($id) {
			$this->add( $id, DataManager::class );
		});

		$this->register_service('dynamic_lists_api_client', function ($id) {
			$this->add( $id, APIClient::class )
				->addArgument( $this->get_internal( 'options' ) );
		});

		$this->register_service('dynamic_lists', function ($id) {
			$this->add( $id, DynamicLists::class )
				->addArgument( $this->get_internal( 'dynamic_lists_api_client' ) )
				->addArgument( $this->get_internal( 'dynamic_lists_data_manager' ) )
				->addArgument( $this->get_internal( 'user' ) )
				->addArgument( $this->get_internal( 'template_path' ) )
				->addArgument( $this->get_external( 'beacon' ) );
		});

		$this->register_service('dynamic_lists_subscriber', function ($id) {
			$this->share( $id, Subscriber::class )
				->addArgument( $this->get_internal( 'dynamic_lists' ) );
		});
	}
}
