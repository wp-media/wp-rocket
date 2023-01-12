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

	/**
	 * Registers the option array in the container
	 *
	 * @return void
	 */
	public function register() {
		$this->add( 'dynamic_lists_data_manager', DataManager::class );
		$this->add( 'dynamic_lists_api_client', APIClient::class )
			->addArgument( $this->get_internal( 'options' ) );
		$this->add( 'dynamic_lists', DynamicLists::class )
			->addArgument( $this->get_internal( 'dynamic_lists_api_client' ) )
			->addArgument( $this->get_internal( 'dynamic_lists_data_manager' ) )
			->addArgument( $this->get_internal( 'user' ) )
			->addArgument( $this->get_internal( 'template_path' ) )
			->addArgument( $this->get_internal( 'beacon' ) );

		$this->share( 'dynamic_lists_subscriber', Subscriber::class )
			->addArgument( $this->get_internal( 'dynamic_lists' ) );
	}
}
