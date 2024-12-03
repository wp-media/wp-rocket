<?php
declare(strict_types=1);

namespace WP_Rocket\Addon\Cloudflare;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Addon\Cloudflare\Admin\Subscriber as CloudflareAdminSubscriber;
use WP_Rocket\Addon\Cloudflare\API\{Client, Endpoints};
use WP_Rocket\Addon\Cloudflare\Cloudflare;
use WP_Rocket\Addon\Cloudflare\Subscriber as CloudflareSubscriber;
use WP_Rocket\Admin\Options;
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
		APIKeyFactory::class,
		Client::class,
		Endpoints::class,
		Cloudflare::class,
		CloudflareSubscriber::class,
		CloudflareAdminSubscriber::class,
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
		$this->getContainer()->add( APIKeyFactory::class )->addArgument( Options_Data::class );

		$this->getContainer()->add( Client::class )
			->addArgument( $this->getContainer()->get( APIKeyFactory::class )->create() );
		$this->getContainer()->add( Endpoints::class )
			->addArgument( Client::class );

		$this->getContainer()->add( Cloudflare::class )
			->addArguments(
				[
					Options_Data::class,
					Endpoints::class,
				]
			);
		$this->getContainer()->addShared( CloudflareSubscriber::class )
			->addArguments(
				[
					Cloudflare::class,
					Options_Data::class,
					Options::class,
					APIKeyFactory::class,
				]
			);
		$this->getContainer()->addShared( CloudflareAdminSubscriber::class );
	}
}
