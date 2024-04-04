<?php

namespace WP_Rocket\Engine\Admin\DomainChange;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Common\Ajax\AjaxHandler;

class ServiceProvider extends AbstractServiceProvider {
	/**
	 * Array of services provided by this service provider
	 *
	 * @var array
	 */
	protected $provides = [
		'domain_change_subscriber',
		'ajax_handler',
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
		$this->getContainer()->add( 'ajax_handler', AjaxHandler::class );
		$this->getContainer()->add( 'domain_change_subscriber', Subscriber::class )
			->addArgument( $this->getContainer()->get( 'ajax_handler' ) )
			->addArgument( $this->getContainer()->get( 'beacon' ) );
	}
}
