<?php
namespace WP_Rocket\Engine\CriticalPath;

use League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Admin\Options_Data;

/**
 * Service provider for WP Rocket Optimization.
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
	];

	/**
	 * Registers the subscribers in the container.
	 *
	 * @since 3.6
	 */
	public function register() {
		$options = $this->getContainer()->get( 'options' );

		// Critical CSS.
		$this->critical_css( $options );
	}

	/**
	 * Adds Critical CSS into the Container.
	 *
	 * @since 3.6
	 *
	 * @param Options_Data $options Instance of options.
	 */
	protected function critical_css( Options_Data $options ) {
		$this->getContainer()->add( 'critical_css_generation', 'WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration' );
		$this->getContainer()->add( 'critical_css', 'WP_Rocket\Engine\CriticalPath\CriticalCSS' )
			->withArgument( $this->getContainer()->get( 'critical_css_generation' ) );
		$this->getContainer()->share( 'critical_css_subscriber', 'WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber' )
			->withArgument( $this->getContainer()->get( 'critical_css' ) )
			->withArgument( $this->getContainer()->get( 'options' ) );
	}
}
