<?php
namespace WP_Rocket\Engine\Capabilities;

use WP_Rocket\AbstractServiceProvider;

/**
 * Service Provider for capabilities
 *
 * @since 3.6.3
 */
class ServiceProvider extends AbstractServiceProvider {

	public function get_common_subscribers(): array
	{
		return [
			$this->generate_container_id('capabilities_subscriber')
		];
	}

	public function declare()
	{
		$this->register_service('capabilities_manager', function ($id) {
			$this->add( $id, Manager::class );
		});

		$this->register_service('capabilities_subscriber', function ($id) {
			$this->share( $id, Subscriber::class )
				->addArgument( $this->get_internal( 'capabilities_manager' ) )
				->addTag( 'common_subscriber' );
		});
	}
}
