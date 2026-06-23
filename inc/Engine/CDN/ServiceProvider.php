<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN;

use WP_Rocket\Dependencies\League\Container\Argument\Literal\StringArgument;
use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\CDN\Admin\Subscriber as AdminSubscriber;
use WP_Rocket\Engine\CDN\Drivers\{
	Custom,
	DriverFactory,
	RocketCDNFree,
	RocketCDNPaid
};
use WP_Rocket\Engine\CDN\Render\{
	Controller as RenderController,
	Subscriber as RenderSubscriber
};

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
		'cdn_render_controller',
		'cdn_render_subscriber',
		'cdn_driver_factory',
		'cdn_driver_free',
		'cdn_driver_paid',
		'cdn_driver_byocdn',
		'cdn_driver',
		'cache_controller',
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
		$this->getContainer()->add( 'cache_controller', Cache::class )
			->addArgument( 'rocketcdn_query' );
		$this->getContainer()->addShared( 'cdn', CDN::class )
			->addArgument( 'options' );
		$this->getContainer()->addShared( 'cdn_context', Context::class )
			->addArguments(
				[
					'options',
					'rocketcdn_subscription_controller',
				]
			);

		// Register individual drivers.
		$this->getContainer()->add(
			'cdn_driver_free',
			RocketCDNFree::class
		)->addArgument( 'rocketcdn_query' );

		$this->getContainer()->add(
			'cdn_driver_paid',
			RocketCDNPaid::class
		)->addArgument( 'options' );

		$this->getContainer()->add(
			'cdn_driver_byocdn',
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
					'rocketcdn_subscription_controller',
					'cache_controller',
					'rocketcdn_query',
					'cdn_driver',
				]
			);
		$this->getContainer()->addShared( 'cdn_admin_subscriber', AdminSubscriber::class );

		// CDN Render controller.
		$this->getContainer()->addShared( 'cdn_render_controller', RenderController::class )
			->addArguments(
				[
					'beacon',
					new StringArgument( rocket_get_constant( 'WP_ROCKET_PATH', '' ) . 'views/settings/' ),
					'cdn_context',
					'options',
					'rocketcdn_query',
					'rocketcdn_subscription_controller',
					'user',
					'cache_controller',
				]
			);

		// CDN Render subscriber.
		$this->getContainer()->addShared( 'cdn_render_subscriber', RenderSubscriber::class )
			->addArgument( 'cdn_render_controller' );
	}
}
