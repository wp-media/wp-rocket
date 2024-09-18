<?php
namespace WP_Rocket\Engine\CDN;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\CDN\Admin\Subscriber as AdminSubscriber;

/**
 * Service provider for WP Rocket CDN
 */
class ServiceProvider extends AbstractServiceProvider {
	/**
	 * Array of services provided by this service provider
	 *
	 * @var array
	 */
	protected $provides = [
		'cdn',
		'cdn_subscriber',
		'cdn_admin_subscriber',
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
	 *
	 * @return void
	 */
	public function register(): void {
		$options = $this->getContainer()->get( 'options' );

		$this->getContainer()->addShared( 'cdn', CDN::class )
			->addArgument( $options );
		$this->getContainer()->addShared( 'cdn_subscriber', Subscriber::class )
			->addArgument( $options )
			->addArgument( $this->getContainer()->get( 'cdn' ) );
		$this->getContainer()->addShared( 'cdn_admin_subscriber', AdminSubscriber::class );
	}
}
