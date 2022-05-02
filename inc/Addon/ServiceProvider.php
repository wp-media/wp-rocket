<?php
namespace WP_Rocket\Addon;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Admin\Options_Data;

/**
 * Service provider for WP Rocket addons.
 *
 * @since 3.3
 * @since 3.5 - renamed and moved into this module.
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
		'sucuri_subscriber',
	];

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$options = $this->getContainer()->get( 'options' );

		// Sucuri Addon.
		$this->getContainer()->share( 'sucuri_subscriber', 'WP_Rocket\Addon\Sucuri\Subscriber' )
			->addArgument( $options )
			->addTag( 'common_subscriber' );

		// Cloudflare Addon.
		$this->addon_cloudflare( $options );
	}

	/**
	 * Adds Cloudflare Addon into the Container when the addon is enabled.
	 *
	 * @since 3.5
	 *
	 * @param Options_Data $options Instance of options.
	 */
	protected function addon_cloudflare( Options_Data $options ) {
		// If the option is not enabled, bail out. Don't load the addon.
		if ( ! (bool) $options->get( 'do_cloudflare', false ) ) {
			return;
		}

		$this->provides[] = 'cloudflare_subscriber';

		$this->getContainer()->add( 'cloudflare_api', 'WPMedia\Cloudflare\APIClient' )
			->addArgument( rocket_get_constant( 'WP_ROCKET_VERSION' ) );
		$this->getContainer()->add( 'cloudflare', 'WPMedia\Cloudflare\Cloudflare' )
			->addArgument( $options )
			->addArgument( $this->getContainer()->get( 'cloudflare_api' ) );
		$this->getContainer()->share( 'cloudflare_subscriber', 'WPMedia\Cloudflare\Subscriber' )
			->addArgument( $this->getContainer()->get( 'cloudflare' ) )
			->addArgument( $options )
			->addArgument( $this->getContainer()->get( 'options_api' ) )
			->addTag( 'cloudflare_subscriber' );
	}
}
