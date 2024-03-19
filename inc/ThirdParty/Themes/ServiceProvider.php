<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Themes;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\{AbstractServiceProvider, BootableServiceProviderInterface};

class ServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface {
	/**
	 * The provides array is a way to let the container
	 * know that a service is provided by this service
	 * provider. Every service that is registered via
	 * this service provider must have an alias added
	 * to this array or it will be ignored.
	 *
	 * @var array
	 */
	protected $provides = [];

	/**
	 * Register the service in the provider array
	 *
	 * @return void
	 */
	public function boot() {
		$theme = ThemeResolver::get_current_theme();

		if ( ! empty( $theme ) ) {
			$this->provides[] = $theme;
		}
	}

	/**
	 * Registers the subscribers in the container
	 *
	 * @return void
	 */
	public function register() {
		$theme = ThemeResolver::get_current_theme();

		if ( ! empty( $theme ) ) {
			$factory = new SubscriberFactory();

			$theme_data = $factory->get_subscriber();
			$arguments  = [];

			if ( empty( $theme_data ) ) {
				return;
			}

			foreach ( $theme_data['arguments'] as $arg ) {
				$arguments[] = $this->getContainer()->get( $arg );
			}

			$this->getContainer()
				->share( $theme, $theme_data['class'] )
				->addArguments( $arguments );
		}
	}
}
