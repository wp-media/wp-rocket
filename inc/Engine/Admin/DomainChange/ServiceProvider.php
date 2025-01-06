<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\DomainChange;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Common\Ajax\AjaxHandler;

class ServiceProvider extends AbstractServiceProvider {
	/**
	 * Array of services provided by this service provider
	 *
	 * @var array
	 */
	protected $provides = [
		'domain_change_subscriber',
		AjaxHandler::class,
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
		$this->getContainer()->add( AjaxHandler::class );
		$this->getContainer()->addShared( 'domain_change_subscriber', Subscriber::class )
			->addArguments(
				[
					AjaxHandler::class,
					Beacon::class,
				]
			);
	}
}
