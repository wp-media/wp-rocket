<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints\Activation;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Media\AboveTheFold\Context\Context;
use WP_Rocket\Engine\Media\AboveTheFold\WarmUp\{APIClient, Controller as WarmUpController, Queue};

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
		'atf_context',
		'warmup_apiclient',
		'warmup_controller',
		'atf_activation',
		'warmup_queue',
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
		$this->getContainer()->add( 'atf_context', Context::class );

		$this->getContainer()->add( 'warmup_apiclient', APIClient::class )
			->addArgument( $this->getContainer()->get( 'options' ) );

		$this->getContainer()->add( 'warmup_queue', Queue::class );

		$this->getContainer()->add( 'warmup_controller', WarmUpController::class )
			->addArguments(
				[
					$this->getContainer()->get( 'atf_context' ),
					$this->getContainer()->get( 'options' ),
					$this->getContainer()->get( 'warmup_apiclient' ),
					$this->getContainer()->get( 'user' ),
					$this->getContainer()->get( 'warmup_queue' ),
				]
			);

		$this->getContainer()->add( 'atf_activation', Activation::class )
			->addArguments(
				[
					$this->getContainer()->get( 'warmup_controller' ),
					$this->getContainer()->get( 'atf_context' ),
				]
			);
	}
}
