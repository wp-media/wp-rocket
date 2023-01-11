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
			$this->getInternal('dynamic_lists_subscriber')
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
			->addArgument( $this->getInternal( 'options' ) );
		$this->add( 'dynamic_lists', DynamicLists::class )
			->addArgument( $this->getInternal( 'dynamic_lists_api_client' ) )
			->addArgument( $this->getInternal( 'dynamic_lists_data_manager' ) )
			->addArgument( $this->getInternal( 'user' ) )
			->addArgument( $this->getInternal( 'template_path' ) )
			->addArgument( $this->getInternal( 'beacon' ) );

		$this->share( 'dynamic_lists_subscriber', Subscriber::class )
			->addArgument( $this->getInternal( 'dynamic_lists' ) );
	}
}
