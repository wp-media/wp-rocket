<?php
namespace WP_Rocket\Engine\HealthCheck;

use WP_Rocket\AbstractServiceProvider;

/**
 * Service Provider for health check subscribers
 *
 * @since 3.6
 */
class ServiceProvider extends AbstractServiceProvider {

	public function get_admin_subscribers(): array
	{
		return [
			$this->getInternal('health_check')
		];
	}

	public function get_common_subscribers(): array
	{
		return [
			$this->getInternal('action_scheduler_check')
		];
	}

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$this->share( 'health_check', HealthCheck::class )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addTag( 'admin_subscriber' );
		$this->share( 'action_scheduler_check', ActionSchedulerCheck::class )
			->addTag( 'common_subscriber' );
	}
}
