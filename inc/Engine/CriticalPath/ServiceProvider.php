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
		'cpcss_api_client',
		'cpcss_data_manager',
		'cpcss_service',
		'rest_cpcss_wp_post',
		'rest_cpcss_subscriber',
		'critical_css_admin_subscriber',
	];

	/**
	 * Registers the subscribers in the container.
	 *
	 * @since 3.6
	 */
	public function register() {
		$filesystem        = rocket_direct_filesystem();
		$options           = $this->getContainer()->get( 'options' );
		$critical_css_path = rocket_get_constant( 'WP_ROCKET_CRITICAL_CSS_PATH' );

		$this->getContainer()->share( 'cpcss_api_client', 'WP_Rocket\Engine\CriticalPath\APIClient' );
		$this->getContainer()->share( 'cpcss_data_manager', 'WP_Rocket\Engine\CriticalPath\DataManager' )
			->withArgument( $critical_css_path )
			->withArgument( $filesystem );
		$this->getContainer()->share( 'cpcss_service', 'WP_Rocket\Engine\CriticalPath\ProcessorService' )
			->withArgument( $this->getContainer()->get( 'cpcss_data_manager' ) )
			->withArgument( $this->getContainer()->get( 'cpcss_api_client' ) );

		$processor_service = $this->getContainer()->get( 'cpcss_service' );

		// REST CPCSS START.
		$this->getContainer()->share( 'rest_cpcss_wp_post', 'WP_Rocket\Engine\CriticalPath\RESTWPPost' )
			->withArgument( $processor_service )
			->withArgument( $options );
		$this->getContainer()->share( 'rest_cpcss_subscriber', 'WP_Rocket\Engine\CriticalPath\RESTCSSSubscriber' )
			->withArgument( $this->getContainer()->get( 'rest_cpcss_wp_post' ) );
		// REST CPCSS END.

		$this->getContainer()->add( 'critical_css_generation', 'WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration' )
			->withArgument( $processor_service );
		$this->getContainer()->add( 'critical_css', 'WP_Rocket\Engine\CriticalPath\CriticalCSS' )
			->withArgument( $this->getContainer()->get( 'critical_css_generation' ) )
			->withArgument( $options )
			->withArgument( $filesystem );

		$critical_css = $this->getContainer()->get( 'critical_css' );

		$this->getContainer()->share( 'critical_css_subscriber', 'WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber' )
			->withArgument( $critical_css )
			->withArgument( $processor_service )
			->withArgument( $options )
			->withArgument( $filesystem );

		$this->getContainer()->share( 'critical_css_admin_subscriber', 'WP_Rocket\Engine\CriticalPath\AdminSubscriber' )
			->withArgument( $options )
			->withArgument( $this->getContainer()->get( 'beacon' ) )
			->withArgument( $critical_css )
			->withArgument( $critical_css_path )
			->withArgument( $this->getContainer()->get( 'template_path' ) . '/cpcss' );
	}
}
