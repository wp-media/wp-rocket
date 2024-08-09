<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\LazyRenderContent;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Optimization\LazyRenderContent\Frontend\{Controller as FrontController, Subscriber as FrontSubscriber};
use WP_Rocket\Engine\Optimization\LazyRenderContent\Frontend\Processor\Processor;

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
		'lrc_frontend_processor',
		'lrc_frontend_controller',
		'lrc_frontend_subscriber',
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
	 * Registers the classes in the container
	 *
	 * @return void
	 */
	public function register(): void {
		$this->getContainer()->add( 'lrc_frontend_processor', Processor::class );
		$this->getContainer()->add( 'lrc_frontend_controller', FrontController::class )
			->addArguments(
				[
					$this->getContainer()->get( 'lrc_frontend_processor' ),
				]
			);
		$this->getContainer()->addShared( 'lrc_frontend_subscriber', FrontSubscriber::class )
			->addArguments(
				[
					$this->getContainer()->get( 'lrc_frontend_controller' ),
				]
			);
	}
}
