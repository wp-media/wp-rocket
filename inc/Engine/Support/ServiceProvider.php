<?php

namespace WP_Rocket\Engine\Support;

use WP_Rocket\AbstractServiceProvider;

class ServiceProvider extends AbstractServiceProvider {

	public function get_common_subscribers(): array
	{
		return [
			$this->generate_container_id('support_subscriber')
		];
	}

	public function declare()
	{
		$this->register_service('support_data', function ($id) {
			$this->add( $id, Data::class )
				->addArgument( $this->get_external( 'options' ) );
		});

		$this->register_service('rest_support', function ($id) {
			$this->add( $id, Rest::class )
				->addArgument( $this->get_internal( 'support_data' ) )
				->addArgument( $this->get_external( 'options' ) );
		});

		$this->register_service('support_subscriber', function ($id) {
			$this->share( $id, Subscriber::class )
				->addArgument( $this->get_internal( 'rest_support' ) )
				->addTag( 'common_subscriber' );
		});

	}
}
