<?php
namespace WP_Rocket\Engine\Optimization\DeferJS;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for the WP Rocket Defer JS
 *
 * @since 3.8
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
		'defer_js',
		'defer_js_admin_subscriber',
		'defer_js_subscriber',
	];

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->add( 'defer_js', DeferJS::class )
			->addArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->share( 'defer_js_admin_subscriber', AdminSubscriber::class )
			->addArgument( $this->getContainer()->get( 'defer_js' ) )
			->addTag( 'admin_subscriber' );
		$this->getContainer()->share( 'defer_js_subscriber', Subscriber::class )
			->addArgument( $this->getContainer()->get( 'defer_js' ) )
			->addTag( 'front_subscriber' );
	}
}
