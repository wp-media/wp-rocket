<?php
namespace WP_Rocket\Engine\Optimization;

use WP_Rocket\AbstractServiceProvider;
use WP_Rocket\Engine\Admin\Beacon\ServiceProvider as BeaconServiceProvider;

/**
 * Service provider for the WP Rocket optimizations
 *
 * @since 3.5.3
 */
class AdminServiceProvider extends AbstractServiceProvider {

	public function get_admin_subscribers(): array
	{
		return [
			$this->generate_container_id('minify_css_admin_subscriber'),
			$this->generate_container_id('google_fonts_admin_subscriber'),
			$this->generate_container_id('minify_admin_subscriber'),
		];
	}

	public function declare()
	{
		$this->register_service('minify_css_admin_subscriber', function ($id) {
			$this->share( $id, 'WP_Rocket\Engine\Optimization\Minify\CSS\AdminSubscriber' )
				->addTag( 'admin_subscriber' );
		});

		$this->register_service('google_fonts_settings', function ($id) {
			$this->add( $id, 'WP_Rocket\Engine\Optimization\GoogleFonts\Admin\Settings' )
				->addArgument( $this->get_external( 'options' ) )
				->addArgument( $this->get_external( 'beacon', BeaconServiceProvider::class ) )
				->addArgument( $this->get_external( 'template_path' ) );
		});

		$this->register_service('google_fonts_admin_subscriber', function ($id) {
			$this->share( $id, 'WP_Rocket\Engine\Optimization\GoogleFonts\Admin\Subscriber' )
				->addArgument( $this->get_internal( 'google_fonts_settings' ) )
				->addTag( 'admin_subscriber' );
		});
		$this->register_service('minify_admin_subscriber', function ($id) {
			$this->share( $id, 'WP_Rocket\Engine\Optimization\Minify\AdminSubscriber' )
				->addTag( 'admin_subscriber' )
				->addArgument( $this->get_external( 'options' ) );
		});
	}
}
