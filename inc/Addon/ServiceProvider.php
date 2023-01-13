<?php
namespace WP_Rocket\Addon;

use WP_Rocket\AbstractServiceProvider;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Addon\Sucuri\Subscriber as SucuriSubscriber;
use WPMedia\Cloudflare\APIClient;
use WPMedia\Cloudflare\Cloudflare;
use WPMedia\Cloudflare\Subscriber as CloudflareSubscriber;

/**
 * Service provider for WP Rocket addons.
 *
 * @since 3.3
 * @since 3.5 - renamed and moved into this module.
 */
class ServiceProvider extends AbstractServiceProvider {

	public function get_common_subscribers(): array
	{
		return [
			$this->generate_container_id('sucuri_subscriber'),
		];
	}

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {

		parent::register();

		$options = $this->get_external( 'options' );
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

		$this->add( 'cloudflare_api', APIClient::class )
			->addArgument( rocket_get_constant( 'WP_ROCKET_VERSION' ) );
		$this->add( 'cloudflare', Cloudflare::class )
			->addArgument( $options )
			->addArgument( $this->get_internal( 'cloudflare_api' ) );
		$this->share( 'cloudflare_subscriber', CloudflareSubscriber::class )
			->addArgument( $this->get_internal( 'cloudflare' ) )
			->addArgument( $options )
			->addArgument( $this->get_internal( 'options_api' ) )
			->addTag( 'cloudflare_subscriber' );
	}

	public function declare()
	{
		$this->register_service('sucuri_subscriber', function ($id) {
			$this->share( $id, SucuriSubscriber::class )
				->addArgument( $this->get_external( 'options' ) )
				->addTag( 'common_subscriber' );
		});
	}
}
