<?php
namespace WP_Rocket\ServiceProvider;

use League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for the WP Rocket settings
 *
 * @since 3.3
 * @author Remy Perona
 */
class Settings extends AbstractServiceProvider {

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
		'settings',
		'settings_render',
		'settings_page',
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
		$this->getContainer()->add( 'settings', 'WP_Rocket\Admin\Settings\Settings' )
			->withArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->add( 'settings_render', 'WP_Rocket\Admin\Settings\Render' )
			->withArgument( $this->getContainer()->get( 'template_path' ) . '/settings' );
		$this->getContainer()->add( 'settings_page', 'WP_Rocket\Admin\Settings\Page' )
			->withArgument( $this->getContainer()->get( 'settings_page_config' ) )
			->withArgument( $this->getContainer()->get( 'settings' ) )
			->withArgument( $this->getContainer()->get( 'settings_render' ) )
			->withArgument( $this->getContainer()->get( 'beacon' ) )
			->withArgument( $this->getContainer()->get( 'db_optimization' ) );
	}
}
