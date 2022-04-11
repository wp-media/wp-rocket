<?php
namespace WP_Rocket\Engine\Optimization;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for the WP Rocket optimizations
 *
 * @since 3.5.3
 */
class AdminServiceProvider extends AbstractServiceProvider {

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
		'minify_css_admin_subscriber',
		'google_fonts_settings',
		'google_fonts_admin_subscriber',
		'minify_admin_subscriber',
	];

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->share( 'minify_css_admin_subscriber', 'WP_Rocket\Engine\Optimization\Minify\CSS\AdminSubscriber' )
			->addTag( 'admin_subscriber' );
		$this->getContainer()->add( 'google_fonts_settings', 'WP_Rocket\Engine\Optimization\GoogleFonts\Admin\Settings' )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addArgument( $this->getContainer()->get( 'beacon' ) )
			->addArgument( $this->getContainer()->get( 'template_path' ) );
		$this->getContainer()->share( 'google_fonts_admin_subscriber', 'WP_Rocket\Engine\Optimization\GoogleFonts\Admin\Subscriber' )
			->addArgument( $this->getContainer()->get( 'google_fonts_settings' ) )
			->addTag( 'admin_subscriber' );
		$this->getContainer()->share( 'minify_admin_subscriber', 'WP_Rocket\Engine\Optimization\Minify\AdminSubscriber' )
			->addTag( 'admin_subscriber' )
			->addArgument( $this->getContainer()->get( 'options' ) );
	}
}
