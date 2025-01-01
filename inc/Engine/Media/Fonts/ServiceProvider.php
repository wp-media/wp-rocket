<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\Fonts;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Media\Fonts\Context\OptimizationContext;
use WP_Rocket\Engine\Media\Fonts\Context\SaasContext;
use WP_Rocket\Engine\Media\Fonts\Admin\Data;
use WP_Rocket\Engine\Media\Fonts\Admin\Settings;
use WP_Rocket\Engine\Media\Fonts\Admin\Subscriber as AdminSubscriber;
use WP_Rocket\Engine\Media\Fonts\Clean\Clean;
use WP_Rocket\Engine\Media\Fonts\Clean\Subscriber as CleanSubscriber;
use WP_Rocket\Engine\Media\Fonts\Frontend\Controller as FrontendController;
use WP_Rocket\Engine\Media\Fonts\Frontend\Subscriber as FrontendSubscriber;

/**
 * Service provider for the WP Rocket Font Optimization
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
		'media_fonts_filesystem',
		'media_fonts_settings',
		'media_fonts_data',
		'media_fonts_admin_subscriber',
		'media_fonts_optimization_context',
		'media_fonts_saas_context',
		'media_fonts_frontend_controller',
		'media_fonts_frontend_subscriber',
		'media_fonts_clean',
		'media_fonts_clean_subscriber',
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

		$this->getContainer()->add( 'media_fonts_filesystem', Filesystem::class )
			->addArgument( rocket_direct_filesystem() );

		$this->getContainer()->add( 'media_fonts_settings', Settings::class );
		$this->getContainer()->add( 'media_fonts_data', Data::class )
			->addArgument( 'options' );
		$this->getContainer()->addShared( 'media_fonts_admin_subscriber', AdminSubscriber::class )
			->addArguments(
				[
					'media_fonts_settings',
					'media_fonts_data',
				]
			);

		$this->getContainer()->add( 'media_fonts_clean', Clean::class )
			->addArgument( 'media_fonts_filesystem' );

		$this->getContainer()->addShared( 'media_fonts_clean_subscriber', CleanSubscriber::class )
			->addArgument( 'media_fonts_clean' );

		$this->getContainer()->add( 'media_fonts_optimization_context', OptimizationContext::class )
			->addArgument( 'options' );
		$this->getContainer()->add( 'media_fonts_saas_context', SaasContext::class )
			->addArgument( 'options' );

		$this->getContainer()->add( 'media_fonts_frontend_controller', FrontendController::class )
			->addArguments(
				[
					'media_fonts_optimization_context',
					'media_fonts_saas_context',
					'media_fonts_filesystem',
				]
			);
		$this->getContainer()->addShared( 'media_fonts_frontend_subscriber', FrontendSubscriber::class )
			->addArgument( 'media_fonts_frontend_controller' );
	}
}
