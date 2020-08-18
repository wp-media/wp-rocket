<?php
namespace WP_Rocket\Engine\Optimization\DelayJS;

use WP_Rocket\Engine\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for the WP Rocket Delay JS
 *
 * @since  3.7
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
		'delay_js_settings',
		'delay_js_admin_subscriber',
	];

	/**
	 * Registers the option array in the container
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->add( 'delay_js_settings', 'WP_Rocket\Engine\Optimization\DelayJS\Admin\Settings' )
			->withArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->share( 'delay_js_admin_subscriber', 'WP_Rocket\Engine\Optimization\DelayJS\Admin\Subscriber' )
			->withArgument( $this->getContainer()->get( 'delay_js_settings' ) )
			->withArgument( $this->getContainer()->get( 'template_path' ) . '/settings' );
	}
}
