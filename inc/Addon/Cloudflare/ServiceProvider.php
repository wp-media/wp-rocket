<?php
declare(strict_types=1);

namespace WP_Rocket\Addon\Cloudflare;

use WP_Rocket\Addon\Cloudflare\Admin\Subscriber as CloudflareAdminSubscriber;
use WP_Rocket\Addon\Cloudflare\API\{Client, Endpoints};
use WP_Rocket\Addon\Cloudflare\Auth\APIKey;
use WP_Rocket\Addon\Cloudflare\Cloudflare;
use WP_Rocket\Addon\Cloudflare\Subscriber as CloudflareSubscriber;
use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WPMedia\Cloudflare\Auth\APIKeyFactory;

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

		$this->getLeagueContainer()->add( 'cloudflare_auth_factory', APIKeyFactory::class )->addArgument( $options );

		$this->getLeagueContainer()->add( 'cloudflare_client', Client::class )
			->addArgument( $this->getContainer()->get( 'cloudflare_auth_factory' )->create() );
		$this->getLeagueContainer()->add( 'cloudflare_endpoints', Endpoints::class )
			->addArgument( $this->getContainer()->get( 'cloudflare_client' ) );

		$this->getLeagueContainer()->add( 'cloudflare', Cloudflare::class )
			->addArgument( $options )
			->addArgument( $this->getContainer()->get( 'cloudflare_endpoints' ) );
		$this->getLeagueContainer()->share( 'cloudflare_subscriber', CloudflareSubscriber::class )
			->addArgument( $this->getContainer()->get( 'cloudflare' ) )
			->addArgument( $options )
			->addArgument( $this->getContainer()->get( 'options_api' ) )
			->addArgument( $this->getContainer()->get( 'cloudflare_auth_factory' ) )
			->addTag( 'cloudflare_subscriber' );
		$this->getLeagueContainer()->share(
			'cloudflare_admin_subscriber',
			CloudflareAdminSubscriber::class
		);
	}
}
