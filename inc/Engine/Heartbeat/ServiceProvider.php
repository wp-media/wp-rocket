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

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$this->share( 'heartbeat_subscriber', HeartbeatSubscriber::class )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addTag( 'common_subscriber' );
	}
}
