<?php
namespace WP_Rocket\Engine\Admin\Settings;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for the WP Rocket settings.
 */
class ServiceProvider extends AbstractServiceProvider {
	/**
	 * Array of services provided by this service provider
	 *
	 * @var array
	 */
	protected $provides = [
		'settings',
		'settings_render',
		'settings_page',
		'settings_page_subscriber',
	];

	/**
	 * Check if the service provider provides a specific service.
	 *
	 * @param string $id The id of the service.
	 *
	 * @return bool
	 */
	public function provides( string $id ): bool {
		return in_array( $id, $this->provides, true );
	}

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register(): void {
		$this->getContainer()->add( 'settings', Settings::class )
			->addArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->add( 'settings_render', Render::class )
			->addArgument( $this->getContainer()->get( 'template_path' ) . '/settings' );
		$this->getContainer()->add( 'settings_page', Page::class )
			->addArgument( $this->getContainer()->get( 'settings_page_config' ) )
			->addArgument( $this->getContainer()->get( 'settings' ) )
			->addArgument( $this->getContainer()->get( 'settings_render' ) )
			->addArgument( $this->getContainer()->get( 'beacon' ) )
			->addArgument( $this->getContainer()->get( 'db_optimization' ) )
			->addArgument( $this->getContainer()->get( 'user_client' ) )
			->addArgument( $this->getContainer()->get( 'delay_js_sitelist' ) )
			->addArgument( $this->getContainer()->get( 'template_path' ) )
			->addArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->addShared( 'settings_page_subscriber', Subscriber::class )
			->addArgument( $this->getContainer()->get( 'settings_page' ) );
	}
}
