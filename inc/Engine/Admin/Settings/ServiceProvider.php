<?php
namespace WP_Rocket\Engine\Admin\Settings;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

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
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
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
			->addArgument( $this->getContainer()->get( 'user_client' ) );
		$this->getContainer()->share( 'settings_page_subscriber', Subscriber::class )
			->addArgument( $this->getContainer()->get( 'settings_page' ) )
			->addTag( 'admin_subscriber' );
	}
}
