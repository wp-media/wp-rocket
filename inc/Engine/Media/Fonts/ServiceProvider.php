<?php

namespace WP_Rocket\Engine\Media\Fonts;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Media\Fonts\Context\Context;
use WP_Rocket\Engine\Media\Fonts\Frontend\Controller as FrontendController;
use WP_Rocket\Engine\Media\Fonts\Frontend\Subscriber as FrontendSubscriber;
use WP_Rocket\Engine\Media\Fonts\Factory\GoogleFontFactory;

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
		'media_fonts_context',
		'media_fonts_frontend_controller',
		'media_fonts_frontend_subscriber',
		'media_fonts_factory',
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
	 * Registers the classes.
	 *
	 * @return void
	 */
	public function register(): void {
		$this->getContainer()->add( 'media_fonts_context', Context::class );
		$this->getContainer()->add( 'media_fonts_frontend_controller', FrontendController::class )
			->addArgument( 'media_fonts_context' );
		$this->getContainer()->add( 'media_fonts_frontend_subscriber', FrontendSubscriber::class )
			->addArgument( 'media_fonts_frontend_controller' );
		$this->getContainer()->addShared( 'media_fonts_factory', GoogleFontFactory::class );
	}
}
