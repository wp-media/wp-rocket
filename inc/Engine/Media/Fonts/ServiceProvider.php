<?php

declare(strict_types=1);

namespace WP_Rocket\Engine\Media\Fonts;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Media\Fonts\Frontend\Subscriber as FrontendSubscriber;
use WP_Rocket\Engine\Media\Fonts\Controller\Fonts as FontsController;
use WP_Rocket\Engine\Media\Fonts\Controller\Filesystem;

/**
 * Service provider for the WP Rocket Font Optimization
 */
class ServiceProvider extends AbstractServiceProvider {
	/**
	 * Array of services provided by this service provider
	 *
	 * @var array
	 */
	protected $provides = [
		'fonts_frontend_subscriber',
		'fonts_filesystem',
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
	 * Registers the option array in the container
	 *
	 * @return void
	 */
	public function register(): void {

		$this->getContainer()->add( 'fonts_filesystem', Filesystem::class )
			->addArgument( rocket_get_constant( 'WP_ROCKET_FONT_CSS_PATH' ) )
			->addArgument( rocket_direct_filesystem() );

		$this->getContainer()->add( 'fonts_controller', FontsController::class )
			->addArgument( $this->getContainer()->get( 'fonts_filesystem' ) );

		$this->getContainer()->addShared( 'fonts_frontend_subscriber', FrontendSubscriber::class )
			->addArgument( $this->getContainer()->get( 'fonts_controller' ) );
	}
}
