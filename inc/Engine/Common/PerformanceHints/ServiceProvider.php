<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Common\PerformanceHints\AJAX\Subscriber as AjaxSubscriber;

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
        'performance_hints_ajax_subscriber',
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

		$factories = [
			$this->getContainer()->get( 'atf_factory' ),
		];

		$this->getContainer()->addShared( 'performance_hints_ajax_subscriber', AjaxSubscriber::class )
			->addArguments(
				[
					$factories,
				]
			);
	}
}
