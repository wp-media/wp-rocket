<?php
declare(strict_types=1);

namespace WP_Rocket\Addon\Cloudflare;

use WP_Rocket\Addon\Cloudflare\Admin\Subscriber as CloudflareAdminSubscriber;
use WP_Rocket\Addon\Cloudflare\API\{Client, Endpoints};
use WP_Rocket\Addon\Cloudflare\Cloudflare;
use WP_Rocket\Addon\Cloudflare\Subscriber as CloudflareSubscriber;
use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WPMedia\Cloudflare\Auth\APIKeyFactory;

/**
 * Service provider for Cloudflare Addon.
 */
class ServiceProvider extends AbstractServiceProvider {
	/**
	 * Array of services provided by this service provider
	 *
	 * @var array
	 */
	protected $provides = [
		'cloudflare_api_key_factory',
		'cloudflare_client',
		'cloudflare_endpoints',
		'cloudflare',
		'cloudflare_subscriber',
		'cloudflare_admin_subscriber',
	];

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
	 * Registers items with the container
	 */
	public function register(): void {
		$this->getContainer()->add( 'cloudflare_api_key_factory', APIKeyFactory::class )->addArgument( 'options' );

		$this->getContainer()->add( 'cloudflare_client', Client::class )
			->addArgument( $this->getContainer()->get( 'cloudflare_api_key_factory' )->create() );
		$this->getContainer()->add( 'cloudflare_endpoints', Endpoints::class )
			->addArgument( 'cloudflare_client' );

		$this->getContainer()->add( 'cloudflare', Cloudflare::class )
			->addArguments(
				[
					'options',
					'cloudflare_endpoints',
				]
			);
		$this->getContainer()->addShared( 'cloudflare_subscriber', CloudflareSubscriber::class )
			->addArguments(
				[
					'cloudflare',
					'options',
					'options_api',
					'cloudflare_api_key_factory',
				]
			);
		$this->getContainer()->addShared( 'cloudflare_admin_subscriber', CloudflareAdminSubscriber::class );
	}
}
