<?php
namespace WP_Rocket\Engine\CriticalPath;

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
		'critical_css_generation',
		'critical_css',
		'critical_css_subscriber',
		'rest_delete_post_cpcss',
	];

	/**
	 * Registers the subscribers in the container.
	 *
	 * @since 3.6
	 */
	public function register() {
		$this->getContainer()->add( 'critical_css_generation', 'WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration' );
		$this->getContainer()->add( 'critical_css', 'WP_Rocket\Engine\CriticalPath\CriticalCSS' )
			->withArgument( $this->getContainer()->get( 'critical_css_generation' ) );
		$this->getContainer()->share( 'critical_css_subscriber', 'WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber' )
			->withArgument( $this->getContainer()->get( 'critical_css' ) )
			->withArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->share( 'rest_delete_post_cpcss', 'WP_Rocket\Engine\CriticalPath\RESTDelete' )
			->withArgument( rocket_get_constant( 'WP_ROCKET_CRITICAL_CSS_PATH' ) );
	}
}
