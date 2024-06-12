<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Themes;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\{AbstractServiceProvider, BootableServiceProviderInterface};

class ServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface {
	/**
	 * Array of services provided by this service provider
	 *
	 * @var array
	 */
	protected $provides = [];

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
	 * Register the service in the provider array
	 *
	 * @return void
	 */
	public function boot(): void {
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
	public function register(): void {
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
				->addShared( $theme, $theme_data['class'] )
				->addArguments( $arguments );
		}
	}
}
