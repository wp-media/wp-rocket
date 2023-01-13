<?php
namespace WP_Rocket\Engine\Admin;

use WP_Rocket\AbstractServiceProvider;
use WP_Rocket\Engine\Admin\Deactivation\DeactivationIntent;
use WP_Rocket\Engine\Admin\Deactivation\Subscriber;
use WP_Rocket\ThirdParty\Plugins\Optimization\Hummingbird;
use WP_Rocket\Engine\Admin\ServiceProvider as AdminServiceProvider;
/**
 * Service Provider for admin subscribers.
 *
 * @since 3.3
 */
class ServiceProvider extends AbstractServiceProvider {

	public function get_admin_subscribers(): array
	{
		return [
			$this->generate_container_id('deactivation_intent_subscriber'),
			$this->generate_container_id('hummingbird_subscriber'),
			$this->generate_container_id('actionscheduler_admin_subscriber'),
		];
	}

	public function declare()
	{
		$this->register_service('deactivation_intent', function ($id) {
			$this->add( $id, DeactivationIntent::class )
				->addArgument( $this->get_external( 'template_path' ) . '/deactivation-intent' )
				->addArgument( $this->get_external( 'options_api' ) )
				->addArgument( $this->get_external( 'options' ) );
		});

		$this->register_service('deactivation_intent_subscriber', function ($id) {
			$this->share( $id, Subscriber::class )
				->addArgument( $this->get_external( 'deactivation_intent', AdminServiceProvider::class ) )
				->addTag( 'admin_subscriber' );
		});

		$this->register_service('hummingbird_subscriber', function ($id) {
			$this->share( $id, Hummingbird::class )
				->addArgument( $this->get_external( 'options' ) )
				->addTag( 'admin_subscriber' );
		});

		$this->register_service('actionscheduler_admin_subscriber', function ($id) {
			$this->share( $id, ActionSchedulerSubscriber::class );
		});
	}
}
