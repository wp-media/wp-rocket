<?php
namespace WP_Rocket\Engine\Admin;

use WP_Rocket\AbstractServiceProvider;
use WP_Rocket\Engine\Admin\Deactivation\DeactivationIntent;
use WP_Rocket\Engine\Admin\Deactivation\Subscriber;
use WP_Rocket\ThirdParty\Plugins\Optimization\Hummingbird;

/**
 * Service Provider for admin subscribers.
 *
 * @since 3.3
 */
class ServiceProvider extends AbstractServiceProvider {

	public function get_admin_subscribers(): array
	{
		return [
			$this->getInternal('deactivation_intent_subscriber'),
			$this->getInternal('hummingbird_subscriber'),
			$this->getInternal('actionscheduler_admin_subscriber'),
		];
	}

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$options = $this->getContainer()->get( 'options' );

		$this->add( 'deactivation_intent', DeactivationIntent::class )
			->addArgument( $this->getContainer()->get( 'template_path' ) . '/deactivation-intent' )
			->addArgument( $this->getContainer()->get( 'options_api' ) )
			->addArgument( $options );
		$this->share( 'deactivation_intent_subscriber', Subscriber::class )
			->addArgument( $this->getContainer()->get( 'deactivation_intent' ) )
			->addTag( 'admin_subscriber' );
		$this->share( 'hummingbird_subscriber', Hummingbird::class )
			->addArgument( $options )
			->addTag( 'admin_subscriber' );
		$this->share( 'actionscheduler_admin_subscriber', ActionSchedulerSubscriber::class );
	}
}
