<?php
namespace WP_Rocket\Engine\Admin;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service Provider for admin subscribers.
 *
 * @since 3.3
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
		'deactivation_intent',
		'deactivation_intent_subscriber',
		'hummingbird_subscriber',
	];

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$options = $this->getContainer()->get( 'options' );

		$this->getContainer()->add( 'deactivation_intent', 'WP_Rocket\Engine\Admin\Deactivation\DeactivationIntent' )
			->addArgument( $this->getContainer()->get( 'template_path' ) . '/deactivation-intent' )
			->addArgument( $this->getContainer()->get( 'options_api' ) )
			->addArgument( $options );
		$this->getContainer()->share( 'deactivation_intent_subscriber', 'WP_Rocket\Engine\Admin\Deactivation\Subscriber' )
			->addArgument( $this->getContainer()->get( 'deactivation_intent' ) )
			->addTag( 'admin_subscriber' );
		$this->getContainer()->share( 'hummingbird_subscriber', 'WP_Rocket\ThirdParty\Plugins\Optimization\Hummingbird' )
			->addArgument( $options )
			->addTag( 'admin_subscriber' );

	}
}
