<?php

namespace WP_Rocket\Engine\Media\Fonts;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Media\Fonts\Context\Context;
use WP_Rocket\Engine\Media\Fonts\Frontend\Controller as FrontendController;
use WP_Rocket\Engine\Media\Fonts\Frontend\Subscriber as FrontendSubscriber;
use WP_Rocket\Engine\Optimization\GoogleFonts\Combine as GoogleFontCombinerV1;
use WP_Rocket\Engine\Optimization\GoogleFonts\CombineV2 as GoogleFontCombinerV2;

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
		'google_font_combiner_v1',
		'google_font_combiner_v2',
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

		$this->getContainer()->addShared('google_font_combiner_v1', GoogleFontCombinerV1::class);
		$this->getContainer()->addShared('google_font_combiner_v2', GoogleFontCombinerV2::class);

		$this->getContainer()->add( 'media_fonts_frontend_controller', FrontendController::class )
			->addArguments(
				[
					$this->getContainer()->get('media_fonts_context'),
					$this->getContainer()->get('google_font_combiner_v1'),
					$this->getContainer()->get('google_font_combiner_v2'),
				]
			);
		$this->getContainer()->add( 'media_fonts_frontend_subscriber', FrontendSubscriber::class )
			->addArgument(
				$this->getContainer()->get('media_fonts_frontend_controller' )
			);
	}
}
