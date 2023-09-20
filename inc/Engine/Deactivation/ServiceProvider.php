<?php
namespace WP_Rocket\Engine\Deactivation;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Dependencies\League\Container\ServiceProvider\BootableServiceProviderInterface;
use WP_Rocket\Engine\Cache\AdvancedCache;
use WP_Rocket\Engine\Cache\WPCache;
use WP_Rocket\Engine\Capabilities\Manager;
use WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare;
use WP_Rocket\ThirdParty\Plugins\CDN\CloudflareFacade;

/**
 * Service Provider for the activation process.
 *
 * @since 3.6.3
 */
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
	protected $provides = [
		'advanced_cache',
		'capabilities_manager',
		'wp_cache',
		'cloudflare_plugin_subscriber',
	];

	/**
	 * Executes this method when the service provider is registered
	 *
	 * @return void
	 */
	public function boot() {
		$this->getContainer()
			->inflector( DeactivationInterface::class )
			->invokeMethod( 'deactivate', [] );
	}

	/**
	 * Registers the option array in the container.
	 */
	public function register() {
		$filesystem = rocket_direct_filesystem();

		$this->getContainer()->add( 'cloudflare_plugin_facade', CloudflareFacade::class );
		$this->getContainer()
			->share( 'cloudflare_plugin_subscriber', Cloudflare::class )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addArgument( $this->getContainer()->get( 'options_api' ) )
			->addArgument( $this->getContainer()->get( 'beacon' ) )
			->addArgument( $this->getContainer()->get( 'cloudflare_plugin_facade' ) )
			->addTag( 'common_subscriber' );

		$this->getContainer()->add( 'advanced_cache', AdvancedCache::class )
			->addArgument( $this->getContainer()->get( 'template_path' ) . '/cache/' )
			->addArgument( $filesystem );
		$this->getContainer()->add( 'capabilities_manager', Manager::class );
		$this->getContainer()->add( 'wp_cache', WPCache::class )
			->addArgument( $filesystem );
	}
}
