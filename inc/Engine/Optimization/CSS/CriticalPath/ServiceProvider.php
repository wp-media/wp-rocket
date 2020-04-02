<?php
namespace WP_Rocket\Engine\Optimization\CSS\CriticalPath;

use League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for the Critical CSS classes
 *
 * @since 3.6
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
		'rest_generate_post_cpcss',
	];

	/**
	 * Registers in the container
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->share( 'rest_generate_post_cpcss', 'WP_Rocket\Engine\Optimization\CSS\CriticalPath\RESTGenerate' );
	}
}
