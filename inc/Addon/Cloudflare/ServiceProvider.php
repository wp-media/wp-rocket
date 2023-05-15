<?php
declare(strict_types=1);

namespace WP_Rocket\Addon\Cloudflare;

use WP_Rocket\Addon\Cloudflare\Admin\Subscriber as CloudflareAdminSubscriber;
use WP_Rocket\Addon\Cloudflare\API\{Client, Endpoints};
use WP_Rocket\Addon\Cloudflare\Auth\APIKey;
use WP_Rocket\Addon\Cloudflare\Cloudflare;
use WP_Rocket\Addon\Cloudflare\Subscriber as CloudflareSubscriber;
use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for Cloudflare Addon.
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
		'cloudflare_auth',
		'cloudflare_client',
		'cloudflare_endpoints',
		'cloudflare',
		'cloudflare_subscriber',
		'cloudflare_admin_subscriber',
	];

	/**
	 * Registers items with the container
	 */
	public function register() {
		$options = $this->getContainer()->get( 'options' );

		$cf_api_key = defined( 'WP_ROCKET_CF_API_KEY' ) ? rocket_get_constant( 'WP_ROCKET_CF_API_KEY', '' ) : $options->get( 'cloudflare_api_key', '' );

		$this->getContainer()->add( 'cloudflare_auth', APIKey::class )
			->addArgument( $options->get( 'cloudflare_email', '' ) )
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
