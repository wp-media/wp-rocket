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
		'rest_generate_post_cpcss',
		'critical_css_admin_subscriber',
	];

	/**
	 * Registers the subscribers in the container.
	 *
	 * @since 3.6
	 */
	public function register() {
		$options           = $this->getContainer()->get( 'options' );
		$critical_css_path = rocket_get_constant( 'WP_ROCKET_CRITICAL_CSS_PATH' );

		$this->getContainer()->add( 'critical_css_generation', 'WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration' );
		$this->getContainer()->add( 'critical_css', 'WP_Rocket\Engine\CriticalPath\CriticalCSS' )
			->withArgument( $this->getContainer()->get( 'critical_css_generation' ) );
		$this->getContainer()->share( 'critical_css_subscriber', 'WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber' )
			->withArgument( $this->getContainer()->get( 'critical_css' ) )
			->withArgument( $options );
		$this->getContainer()->share( 'rest_delete_post_cpcss', 'WP_Rocket\Engine\CriticalPath\RESTDelete' )
			->withArgument( $critical_css_path );
		$this->getContainer()->share( 'rest_generate_post_cpcss', 'WP_Rocket\Engine\CriticalPath\RESTGenerate' )
			->withArgument( $critical_css_path );
		$this->getContainer()->share( 'critical_css_admin_subscriber', 'WP_Rocket\Engine\CriticalPath\AdminSubscriber' )
			->withArgument( $options )
			->withArgument( $this->getContainer()->get( 'beacon' ) )
			->withArgument( $critical_css_path )
			->withArgument( $this->getContainer()->get( 'template_path' ) . '/metabox/cpcss' );
	}
}
