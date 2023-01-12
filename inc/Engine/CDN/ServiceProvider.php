<?php
namespace WP_Rocket\Engine\CDN;

use WP_Rocket\AbstractServiceProvider;

/**
 * Service provider for WP Rocket CDN
 *
 * @since 3.5.5
 */
class ServiceProvider extends AbstractServiceProvider {

	public function get_common_subscribers(): array
	{
		return [
			$this->generate_container_id('cdn_subscriber')
		];
	}

	public function declare()
	{
		$this->register_service('cdn', function($id) {
			$this->share( $id, CDN::class )
				->addArgument( $this->get_external( 'options' ) );
		});

		$this->register_service('cdn_subscriber', function($id) {
			$this->share( $id, Subscriber::class )
				->addArgument( $this->get_external( 'options' ) )
				->addArgument( $this->get_internal( 'cdn' ) )
				->addTag( 'common_subscriber' );
		});
	}
}
