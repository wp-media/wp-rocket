<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\CDN\Admin\Subscriber as AdminSubscriber;
use WP_Rocket\Engine\CDN\Drivers\Custom;
use WP_Rocket\Engine\CDN\Drivers\DriverFactory;
use WP_Rocket\Engine\CDN\Drivers\RocketCDNFree;
use WP_Rocket\Engine\CDN\Drivers\RocketCDNPaid;

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
		'cdn_context',
		'cdn_subscriber',
		'cdn_admin_subscriber',
		'cdn_driver_factory',
		'cdn_driver.free',
		'cdn_driver.paid',
		'cdn_driver.byocdn',
		'cdn_driver',
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
		$this->getContainer()->addShared( 'cdn', CDN::class )
			->addArgument( 'options' );
		$this->getContainer()->addShared( 'cdn_context', Context::class )
			->addArguments(
				[
					'options',
					'rocketcdn_api_client',
				]
			);

		// Register individual drivers.
		$this->getContainer()->add(
			'cdn_driver.free',
			RocketCDNFree::class
		)->addArgument( 'rocketcdn_query' );

		$this->getContainer()->add(
			'cdn_driver.paid',
			RocketCDNPaid::class
		)->addArgument( 'options' );

		$this->getContainer()->add(
			'cdn_driver.byocdn',
			Custom::class
		);

		// Register Driver Factory.
		$this->getContainer()->addShared(
			'cdn_driver_factory',
			DriverFactory::class
		)
			->addArgument( $this->getContainer() )
			->addArgument( 'cdn_context' );

		// Register current active driver (resolved at runtime).
		$this->getContainer()->add(
			'cdn_driver',
			function () {
				$factory = $this->getContainer()->get( 'cdn_driver_factory' );
				return $factory->create();
			}
		);

		$this->getContainer()->addShared( 'cdn_subscriber', Subscriber::class )
			->addArguments(
				[
					'options',
					'cdn',
					'options_api',
					'cdn_driver',
				]
			);
		$this->getContainer()->addShared( 'cdn_admin_subscriber', AdminSubscriber::class );
	}
}
