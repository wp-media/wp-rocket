<?php
namespace WP_Rocket\Engine\Admin\Settings;

use WP_Rocket\AbstractServiceProvider;
use WP_Rocket\Engine\Admin\Beacon\ServiceProvider as BeaconServiceProvider;
use WP_Rocket\Engine\Admin\Database\ServiceProvider as DatabaseServiceProvider;
use WP_Rocket\Engine\License\ServiceProvider as LicenseServiceProvider;

/**
 * Service provider for the WP Rocket settings.
 *
 * @since 3.5.5 Moves into the new architecture.
 * @since 3.3
 */
class ServiceProvider extends AbstractServiceProvider {

	public function get_admin_subscribers(): array
	{
		return [
			$this->generate_container_id('settings_page_subscriber'),
		];
	}

	public function declare()
	{
		$this->register_service('settings', function($id) {
			$this->add( $id, Settings::class )
				->addArgument( $this->get_external( 'options' ) );
		});

		$this->register_service('settings_render', function ($id) {
			$this->add( $id, Render::class )
				->addArgument( $this->get_external( 'template_path' ) . '/settings' );
		});

		$this->register_service('settings_page', function ($id) {
			$this->add( $id, Page::class )
				->addArgument( $this->get_external( 'settings_page_config' ) )
				->addArgument( $this->get_internal( 'settings' ) )
				->addArgument( $this->get_internal( 'settings_render' ) )
				->addArgument( $this->get_external( 'beacon', BeaconServiceProvider::class ) )
				->addArgument( $this->get_external( 'db_optimization', DatabaseServiceProvider::class ) )
				->addArgument( $this->get_external( 'user_client', LicenseServiceProvider::class ) );
		});

		$this->register_service('settings_page_subscriber', function($id) {
			$this->share( $id, Subscriber::class )
				->addArgument( $this->get_internal( 'settings_page' ) )
				->addTag( 'admin_subscriber' );
		});
	}
}
