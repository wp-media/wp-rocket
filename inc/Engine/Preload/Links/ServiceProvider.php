<?php
namespace WP_Rocket\Engine\Preload\Links;

use WP_Rocket\AbstractServiceProvider;

/**
 * Service provider for WP Rocket preload links.
 */
class ServiceProvider extends AbstractServiceProvider {

	public function get_common_subscribers(): array
	{
		return [
			$this->generate_container_id('preload_links_admin_subscriber'),
			$this->generate_container_id('preload_links_subscriber'),
		];
	}


	public function declare()
	{
		$this->register_service('preload_links_admin_subscriber', function($id) {
			$this->share( $id, AdminSubscriber::class )
				->addArgument( $this->get_external( 'options' ) )
				->addTag( 'common_subscriber' );
		});

		$this->register_service('preload_links_subscriber', function($id) {
			$this->share( $id, Subscriber::class )
				->addArgument( $this->get_external( 'options' ) )
				->addArgument( rocket_direct_filesystem() )
				->addTag( 'common_subscriber' );
		});
	}
}
