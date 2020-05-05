<?php
namespace WP_Rocket\Engine\Admin;

use League\Container\ServiceProvider\AbstractServiceProvider;

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
		'deactivation_intent_render',
		'deactivation_intent_subscriber',
		'hummingbird_subscriber',
	];

	/**
	 * Registers the option array in the container.
	 *
	 * @since 3.3
	 */
	public function register() {
		$options = $this->getContainer()->get( 'options' );

		$this->getContainer()->add( 'deactivation_intent_render', 'WP_Rocket\Admin\Deactivation\Render' )
			->withArgument( $this->getContainer()->get( 'template_path' ) . '/deactivation-intent' );
		$this->getContainer()->share( 'deactivation_intent_subscriber', 'WP_Rocket\Engine\Admin\Deactivation\DeactivationIntent' )
			->withArgument( $this->getContainer()->get( 'deactivation_intent_render' ) )
			->withArgument( $this->getContainer()->get( 'options_api' ) )
			->withArgument( $options );
		$this->getContainer()->share( 'hummingbird_subscriber', 'WP_Rocket\ThirdParty\Plugins\Optimization\Hummingbird' )
			->withArgument( $options );
	}
}
