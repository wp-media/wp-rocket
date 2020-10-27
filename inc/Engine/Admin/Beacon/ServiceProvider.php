<?php
namespace WP_Rocket\Engine\Admin\Beacon;

use WP_Rocket\Engine\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service Provider for Beacon
 *
 * @since 3.3
 * @author Remy Perona
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
		'beacon',
	];

	/**
	 * Registers the option array in the container
	 *
	 * @since 3.3
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->add( 'beacon', 'WP_Rocket\Engine\Admin\Beacon\Beacon' )
			->withArgument( $this->getContainer()->get( 'options' ) )
			->withArgument( $this->getContainer()->get( 'template_path' ) . '/settings' )
			->withArgument( $this->getContainer()->get( 'support_data' ) );
	}
}
