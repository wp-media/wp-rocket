<?php
namespace WP_Rocket\Engine\Optimization\DelayJS;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

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
		'delay_js_html',
		'delay_js_subscriber',
	];

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->add( 'delay_js_settings', 'WP_Rocket\Engine\Optimization\DelayJS\Admin\Settings' )
			->addArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->share( 'delay_js_admin_subscriber', 'WP_Rocket\Engine\Optimization\DelayJS\Admin\Subscriber' )
			->addArgument( $this->getContainer()->get( 'delay_js_settings' ) )
			->addTag( 'admin_subscriber' );
		$this->getContainer()->add( 'delay_js_html', 'WP_Rocket\Engine\Optimization\DelayJS\HTML' )
			->addArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->share( 'delay_js_subscriber', 'WP_Rocket\Engine\Optimization\DelayJS\Subscriber' )
			->addArgument( $this->getContainer()->get( 'delay_js_html' ) )
			->addArgument( rocket_direct_filesystem() )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addTag( 'front_subscriber' );
	}
}
