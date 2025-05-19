<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\PreloadFonts;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Media\PreloadFonts\Database\Table\PreloadFonts as PreloadFontsTable;
use WP_Rocket\Engine\Media\PreloadFonts\Database\Queries\PreloadFonts as PreloadFontsQuery;
use WP_Rocket\Engine\Media\PreloadFonts\AJAX\Controller as AJAXController;
use WP_Rocket\Engine\Media\PreloadFonts\Context\Context;
use WP_Rocket\Engine\Media\PreloadFonts\Frontend\Controller as FrontendController;
use WP_Rocket\Engine\Media\PreloadFonts\Frontend\Subscriber as FrontendSubscriber;
use WP_Rocket\Engine\Media\PreloadFonts\Admin\Subscriber as AdminSubscriber;
use WP_Rocket\Engine\Media\PreloadFonts\Admin\Settings as AdminSettings;

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
		'preload_fonts_table',
		'preload_fonts_query',
		'preload_fonts_ajax_controller',
		'preload_fonts_context',
		'preload_fonts_frontend_subscriber',
		'preload_fonts_front_controller',
		'preload_fonts_factory',
		'preload_fonts_admin_subscriber',
		'preload_fonts_admin_settings',
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
	 * Registers the classes in the container
	 *
	 * @return void
	 */
	public function register(): void {
		$this->getContainer()->addShared( 'preload_fonts_table', PreloadFontsTable::class );

		$this->getContainer()->get( 'preload_fonts_table' );

		$this->getContainer()->add( 'preload_fonts_query', PreloadFontsQuery::class );
		$this->getContainer()->add( 'preload_fonts_context', Context::class )
			->addArgument( 'options' );
		$this->getContainer()->addShared( 'preload_fonts_front_controller', FrontendController::class )
		->addArguments(
			[
				'options',
				'preload_fonts_query',
				'preload_fonts_context',
			]
		);

		$this->getContainer()->add( 'preload_fonts_ajax_controller', AJAXController::class )
			->addArguments(
				[
					'preload_fonts_query',
					'preload_fonts_context',
				]
			);

		$this->getContainer()->addShared( 'preload_fonts_frontend_subscriber', FrontendSubscriber::class )
			->addArguments(
				[
					'preload_fonts_front_controller',
					'dynamic_lists_defaultlists_data_manager',
				]
			);

		$this->getContainer()->addShared( 'preload_fonts_factory', Factory::class )
			->addArguments(
				[
					'preload_fonts_ajax_controller',
					'preload_fonts_front_controller',
					'preload_fonts_table',
					'preload_fonts_query',
					'preload_fonts_context',
				]
			);

		$this->getContainer()->addShared( 'preload_fonts_admin_settings', AdminSettings::class )
			->addArguments(
				[
					'options',
					'options_api',
				]
			);
		$this->getContainer()->add( 'preload_fonts_admin_subscriber', AdminSubscriber::class )
			->addArguments(
				[
					'preload_fonts_admin_settings',
				]
			);
	}
}
