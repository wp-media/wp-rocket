<?php
namespace WP_Rocket\ServiceProvider;

use WP_Rocket\Engine\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for the WP Rocket options
 *
 * @since 3.3
 * @author Remy Perona
 */
class Options extends AbstractServiceProvider {

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
		'options',
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
		$this->getContainer()->add( 'options', 'WP_Rocket\Admin\Options_Data' )
			->withArgument( $this->getContainer()->get( 'options_api' )->get( 'settings', [] ) );
	}
}
