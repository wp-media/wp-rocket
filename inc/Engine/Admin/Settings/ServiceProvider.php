<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\Settings;

use WP_Rocket\Dependencies\League\Container\Argument\Literal\{ArrayArgument, StringArgument};
use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Dependencies\WPMedia\PluginFamily\Model\PluginFamily as PluginFamilyModel;
use WP_Rocket\Dependencies\WPMedia\PluginFamily\Controller\PluginFamily as PluginFamilyController;

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
		'plugin_family_model',
		'plugin_family_controller',
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
		$this->getContainer()->add( 'plugin_family_model', PluginFamilyModel::class );
		$this->getContainer()->add( 'plugin_family_controller', PluginFamilyController::class );

		$this->getContainer()->add( 'settings', Settings::class )
			->addArgument( 'options' );
		$this->getContainer()->add( 'settings_render', Render::class )
			->addArguments(
				[
					new StringArgument( $this->getContainer()->get( 'template_path' ) . '/settings' ),
					'plugin_family_model',
				]
			);
		$this->getContainer()->add( 'settings_page', Page::class )
			->addArguments(
				[
					new ArrayArgument(
						[
							'slug'       => WP_ROCKET_PLUGIN_SLUG,
							'title'      => WP_ROCKET_PLUGIN_NAME,
							'capability' => 'rocket_manage_options',
						]
					),
					'settings',
					'settings_render',
					'beacon',
					'db_optimization',
					'user_client',
					'delay_js_sitelist',
					'template_path',
					'options',
				]
			);
		$this->getContainer()->addShared( 'settings_page_subscriber', Subscriber::class )
			->addArguments(
				[
					'settings_page',
					'plugin_family_controller',
				]
			);
	}
}
