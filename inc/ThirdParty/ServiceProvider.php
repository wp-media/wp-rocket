<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\{AbstractServiceProvider, BootableServiceProviderInterface};
use WP_Rocket\ThirdParty\Plugins\CDN\CloudflareFacade;
use WP_Rocket\ThirdParty\Plugins\ModPagespeed;
use WP_Rocket\ThirdParty\Plugins\Optimization\Ezoic;
use WP_Rocket\ThirdParty\Plugins\PluginResolver;
use WP_Rocket\ThirdParty\Plugins\SubscriberFactory;

/**
 * Service provider for WP Rocket third party compatibility
 */
class ServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface {
	/**
	 * Ids registered unconditionally, outside the plugin resolver.
	 *
	 * @var array<string>
	 */
	private const STATIC_PROVIDES = [ 'ezoic', 'mod_pagespeed', 'cloudflare_plugin_facade' ];

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
	 * Builds the list of provided service ids from the static extras and the
	 * resolver's active plugin ids.
	 *
	 * @return void
	 */
	public function boot(): void {
		$this->provides = array_merge( self::STATIC_PROVIDES, PluginResolver::get_active_plugins() );
	}

	/**
	 * Registers the subscribers in the container
	 *
	 * @since 3.3
	 *
	 * @return void
	 */
	public function register(): void {
		$container = $this->getContainer();

		// Non-resolver dependency + SPECIAL always-load ids.
		$container->add( 'cloudflare_plugin_facade', CloudflareFacade::class );
		$container->addShared( 'ezoic', Ezoic::class );
		$container->addShared( 'mod_pagespeed', ModPagespeed::class );

		$factory  = new SubscriberFactory();
		$registry = $factory->get_registry();

		foreach ( PluginResolver::get_active_plugins() as $id ) {
			if ( ! isset( $registry[ $id ] ) ) {
				continue;
			}

			$definition = $container->addShared( $id, $registry[ $id ] );
			$args       = $factory->get_arguments( $id );

			if ( ! empty( $args ) ) {
				$definition->addArguments( $args );
			}
		}
	}
}
