<?php
namespace WP_Rocket\Engine\Heartbeat;

use WP_Rocket\AbstractServiceProvider;

/**
 * Service provider for Media module
 *
 * @since 3.7
 */
class ServiceProvider extends AbstractServiceProvider {

	public function get_common_subscribers(): array
	{
		return [
			$this->generate_container_id('heartbeat_subscriber')
		];
	}

	public function declare()
	{
		$this->register_service('heartbeat_subscriber', function ($id) {
			$this->share( $id, HeartbeatSubscriber::class )
				->addArgument( $this->get_external( 'options' ) )
				->addTag( 'common_subscriber' );
		});
	}
}
