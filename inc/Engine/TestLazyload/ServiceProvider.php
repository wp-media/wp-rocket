<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\TestLazyload;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

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
		'test_lazyload_subscriber',
		'test_lazyload_class',
	];

	/**
	 * Registers the subscribers in the container
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()
			->share( 'test_lazyload_class', TestClass::class );

		$this->getContainer()
			->share( 'test_lazyload_subscriber', Subscriber::class )
			->addArgument( $this->getContainer()->get( 'test_lazyload_class', true, true ) );


	}
}
