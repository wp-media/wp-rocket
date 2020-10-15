<?php
namespace WP_Rocket\Engine\Admin\Settings;

use WP_Rocket\Engine\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for the WP Rocket settings.
 *
 * @since 3.5.5 Moves into the new architecture.
 * @since 3.3
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
		'settings',
		'settings_render',
		'settings_page',
		'settings_page_subscriber',
	];

	/**
	 * Registers the option array in the container.
	 *
	 * @since 3.3
	 */
	public function register() {
		$this->getContainer()->add( 'settings', 'WP_Rocket\Engine\Admin\Settings\Settings' )
			->withArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->add( 'settings_render', 'WP_Rocket\Engine\Admin\Settings\Render' )
			->withArgument( $this->getContainer()->get( 'template_path' ) . '/settings' );
		$this->getContainer()->add( 'settings_page', 'WP_Rocket\Engine\Admin\Settings\Page' )
			->withArgument( $this->getContainer()->get( 'settings_page_config' ) )
			->withArgument( $this->getContainer()->get( 'settings' ) )
			->withArgument( $this->getContainer()->get( 'settings_render' ) )
			->withArgument( $this->getContainer()->get( 'beacon' ) )
			->withArgument( $this->getContainer()->get( 'db_optimization' ) )
			->withArgument( $this->getContainer()->get( 'user_client' ) );
		$this->getContainer()->share( 'settings_page_subscriber', 'WP_Rocket\Engine\Admin\Settings\Subscriber' )
			->withArgument( $this->getContainer()->get( 'settings_page' ) );
	}
}
