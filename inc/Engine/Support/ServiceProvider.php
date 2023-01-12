<?php

namespace WP_Rocket\Engine\Support;

use WP_Rocket\AbstractServiceProvider;
use WP_Rocket\Engine\Support\Data;
use WP_Rocket\Engine\Support\Rest;
use WP_Rocket\Engine\Support\Subscriber;

class ServiceProvider extends AbstractServiceProvider {

	public function get_common_subscribers(): array
	{
		return [
			$this->generate_container_id('support_subscriber')
		];
	}

	/**
	 * Registers the services in the container
	 *
	 * @return void
	 */
	public function register() {
		$options = $this->getContainer()->get( 'options' );

		$this->add( 'support_data', Data::class )
			->addArgument( $options );
		$this->add( 'rest_support', Rest::class )
			->addArgument( $this->get_internal( 'support_data' ) )
			->addArgument( $options );
		$this->share( 'support_subscriber', Subscriber::class )
			->addArgument( $this->get_internal( 'rest_support' ) )
			->addTag( 'common_subscriber' );
	}
}
