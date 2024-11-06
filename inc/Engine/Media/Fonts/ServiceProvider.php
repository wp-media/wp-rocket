<?php

namespace WP_Rocket\Engine\Media\Fonts;

use WP_Rocket\Engine\Media\Fonts\Frontend\Subscriber as FrontendSubscriber;
use WP_Rocket\Engine\Media\Fonts\Frontend\Controller as FrontendController;
use WP_Rocket\Engine\Media\Fonts\Context;
use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

class ServiceProvider extends AbstractServiceProvider {
	/**
	 * @var array
	 */
	protected $provides = [
		'media_fonts_context',
		'media_fonts_frontend_controller',
		'media_fonts_frontend_subscriber',
	];

	/**
	 * Registers the classes.
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->add('media_fonts_context', Context::class);
		$this->getContainer()->add('media_fonts_frontend_controller', FrontendController::class)
			->withArgument('media_fonts_context');
		$this->getContainer()->add('media_fonts_frontend_subscriber', FrontendSubscriber::class)
			->withArgument('media_fonts_frontend_controller');
	}
}
