<?php
namespace WP_Rocket\Addon\Varnish;

use WP_Rocket\AbstractServiceProvider;

/**
 * Service provider for Varnish Addon.
 */
class ServiceProvider extends AbstractServiceProvider {

	public function get_common_subscribers(): array
	{
		return [
			$this->generate_container_id('varnish_subscriber')
		];
	}

	public function declare()
	{
		$this->register_service('varnish', function ($id) {
			$this->add( $id, Varnish::class );
		});

		$this->register_service('varnish_subscriber', function ($id) {
			$this->share( $id, Subscriber::class )
				->addArgument( $this->get_internal( 'varnish' ) )
				->addArgument( $this->get_external( 'options' ) )
				->addTag( 'common_subscriber' );
		});
	}
}
