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
			$this->getInternal('capabilities_subscriber')
		];
	}

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$this->add( 'capabilities_manager', Manager::class );
		$this->share( 'capabilities_subscriber', Subscriber::class )
			->addArgument( $this->getInternal( 'capabilities_manager' ) )
			->addTag( 'common_subscriber' );
	}
}
