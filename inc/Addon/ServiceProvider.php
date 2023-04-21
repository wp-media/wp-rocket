<?php
namespace WP_Rocket\Addon;

use WP_Rocket\Addon\Cloudflare\Admin\Subscriber as CloudflareAdminSubscriber;
use WP_Rocket\Addon\Cloudflare\API\{Client, Endpoints};
use WP_Rocket\Addon\Cloudflare\Auth\APIKey;
use WP_Rocket\Addon\Cloudflare\Cloudflare;
use WP_Rocket\Addon\Cloudflare\Subscriber as CloudflareSubscriber;
use WP_Rocket\Addon\Sucuri\Subscriber as SucuriSubscriber;
use WP_Rocket\Addon\WebP\AdminSubscriber as WebPAdminSubscriber;
use WP_Rocket\Addon\WebP\Subscriber as WebPSubscriber;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for WP Rocket addons.
 */
class ServiceProvider extends AbstractServiceProvider {

	/**
	 * Provides
	 *
	 * @var array
	 */
	protected $provides = [
		'sucuri_subscriber',
		'webp_subscriber',
		'webp_admin_subscriber',
	];

	/**
	 * Registers items with the container
	 */
	public function register() {
		$options = $this->getContainer()->get( 'options' );

		// Sucuri Addon.
		$this->getContainer()->share( 'sucuri_subscriber', SucuriSubscriber::class )
			->addArgument( $options )
			->addTag( 'common_subscriber' );

		// Cloudflare Addon.
		$this->addon_cloudflare( $options );

		$this->getContainer()->share( 'webp_admin_subscriber', WebPAdminSubscriber::class )
			->addArgument( $options )
			->addArgument( $this->getContainer()->get( 'cdn_subscriber' ) )
			->addArgument( $this->getContainer()->get( 'beacon' ) )
			->addTag( 'common_subscriber' );

		$this->getContainer()->share( 'webp_subscriber', WebPSubscriber::class )
			->addArgument( $options )
			->addArgument( $this->getContainer()->get( 'options_api' ) )
			->addArgument( $this->getContainer()->get( 'cdn_subscriber' ) )
			->addTag( 'common_subscriber' );
	}

	/**
	 * Adds Cloudflare Addon into the Container when the addon is enabled.
	 *
	 * @param Options_Data $options Instance of options.
	 */
	protected function addon_cloudflare( Options_Data $options ) {
		// If the option is not enabled, bail out. Don't load the addon.
		if ( ! (bool) $options->get( 'do_cloudflare', false ) ) {
			return;
		}

		$cf_api_key = defined( 'WP_ROCKET_CF_API_KEY' ) ? rocket_get_constant( 'WP_ROCKET_CF_API_KEY' ) : $options->get( 'cloudflare_api_key', '' );

		if (
			empty( $options->get( 'cloudflare_api_key', '' ) )
			||
			empty( $cf_api_key )
		) {
			return;
		}

		$this->provides[] = 'cloudflare_auth';
		$this->provides[] = 'cloudflare_client';
		$this->provides[] = 'cloudflare_endpoints';
		$this->provides[] = 'cloudflare';
		$this->provides[] = 'cloudflare_subscriber';
		$this->provides[] = 'cloudflare_admin_subscriber';

		$this->getContainer()->add( 'cloudflare_auth', APIKey::class )
			->addArgument( $options->get( 'cloudflare_api_email' ) )
			->addArgument( $cf_api_key );

		$this->getContainer()->add( 'cloudflare_client', Client::class )
			->addArgument( $this->getContainer()->get( 'cloudflare_auth' ) );
		$this->getContainer()->add( 'cloudflare_endpoints', Endpoints::class )
			->addArgument( $this->getContainer()->get( 'cloudflare_client' ) );

		$this->getContainer()->add( 'cloudflare', Cloudflare::class )
			->addArgument( $options )
			->addArgument( $this->getContainer()->get( 'cloudflare_endpoints' ) );
		$this->getContainer()->share( 'cloudflare_subscriber', CloudflareSubscriber::class )
			->addArgument( $this->getContainer()->get( 'cloudflare' ) )
			->addArgument( $options )
			->addArgument( $this->getContainer()->get( 'options_api' ) )
			->addTag( 'cloudflare_subscriber' );
		$this->getContainer()->share(
			'cloudflare_admin_subscriber',
			CloudflareAdminSubscriber::class
		);
	}
}
