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
			$this->generate_container_id('health_check'),
			$this->generate_container_id('action_scheduler_check')
		];
	}

	public function declare()
	{
		$this->register_service('health_check', function ($id) {
			$this->share( $id, HealthCheck::class )
				->addArgument( $this->get_external( 'options' ) )
				->addTag( 'admin_subscriber' );
		});

		$this->register_service('action_scheduler_check', function ($id) {
			$this->share( $id, ActionSchedulerCheck::class )
				->addTag( 'common_subscriber' );
		});
	}
}
