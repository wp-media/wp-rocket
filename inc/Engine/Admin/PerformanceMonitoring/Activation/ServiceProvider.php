<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring\Activation;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Context\ActivationContext;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Queue\Queue as PMQueue;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Activation\Activation as PMActivation;

/**
 * Performance Monitoring Activation ServiceProvider
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
		'pm_activation_context',
		'pm_activation_queue',
		'pm_activation',
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
		// Context for activation - minimal version without dependencies
		$this->getContainer()->add( 'pm_activation_context', ActivationContext::class );

		// Queue for activation - minimal version without dependencies
		$this->getContainer()->add( 'pm_activation_queue', PMQueue::class );

		// Activation class
		$this->getContainer()->add( 'pm_activation', PMActivation::class )
			->addArguments( [
				'pm_activation_queue',
				'pm_activation_context',
			] );
	}
}
