<?php
namespace WP_Rocket\Addon;

use WP_Rocket\Addon\Cloudflare\Admin\Subscriber as CloudflareAdminSubscriber;
use WP_Rocket\Addon\Cloudflare\API\{Client, Endpoints};
use WP_Rocket\Addon\Cloudflare\Auth\APIKey;
use WP_Rocket\Addon\Cloudflare\Cloudflare;
use WP_Rocket\Addon\Cloudflare\Subscriber as CloudflareSubscriber;
use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Addon\Sucuri\Subscriber as SucuriSubscriber;

/**
 * Service provider for WP Rocket addons.
 */
class ServiceProvider extends AbstractServiceProvider {

	/**
	 * @inheritDoc
	 */
	protected $provides = [
		'sucuri_subscriber',
	];

	/**
	 * @inheritDoc
	 */
	public function register() {
		$options = $this->getContainer()->get( 'options' );

		// Sucuri Addon.
		$this->getContainer()->share( 'sucuri_subscriber', SucuriSubscriber::class )
			->addArgument( $options )
			->addTag( 'common_subscriber' );

		// Cloudflare Addon.
		$this->addon_cloudflare( $options );
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

		$this->provides[] = 'cloudflare_auth';
		$this->provides[] = 'cloudflare_client';
		$this->provides[] = 'cloudflare_endpoints';
		$this->provides[] = 'cloudflare';
		$this->provides[] = 'cloudflare_subscriber';
		$this->provides[] = 'cloudflare_admin_subscriber';

		if ( $options->get( 'cloudflare_api_key' ) ) {
			$cf_api_key = defined( 'WP_ROCKET_CF_API_KEY' ) ? WP_ROCKET_CF_API_KEY : $options->get( 'cloudflare_api_key', null );

			$this->getContainer()->add( 'cloudflare_auth', APIKey::class )
				->addArgument( $options->get( 'cloudflare_api_email' ) )
				->addArgument( $cf_api_key );
		}

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
		$this->getContainer()->share( 'cloudflare_admin_subscriber',
		CloudflareAdminSubscriber::class );
	}
}
