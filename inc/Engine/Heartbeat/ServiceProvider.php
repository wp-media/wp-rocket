<?php
namespace WP_Rocket\Engine\Heartbeat;

use WP_Rocket\AbstractServiceProvider;

/**
 * Service provider for Media module
 *
 * @since 3.7
 */
class ServiceProvider extends AbstractServiceProvider {

	/**
	 * The provides array is a way to let the container
	 * know that a service is provided by this service
	 * provider. Every service that is registered via
	 * this service provider must have an alias added
	 * to this array or it will be ignored.
	 *
	 * @var array
	 */
	protected $provides = [
		'heartbeat_subscriber',
	];

	/**
	 * Returns common subscribers.
	 *
	 * @return string[]
	 */
	public function get_common_subscribers(): array {
		return [
			'heartbeat_subscriber',
		];
	}

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->share( 'heartbeat_subscriber', HeartbeatSubscriber::class )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addTag( 'common_subscriber' );
	}
}
