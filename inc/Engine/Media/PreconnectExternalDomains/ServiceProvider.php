<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\PreconnectExternalDomains;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Media\PreconnectExternalDomains\Context\Context;
use WP_Rocket\Engine\Media\PreconnectExternalDomains\Database\Queries\PreconnectExternalDomains as Query;
use WP_Rocket\Engine\Media\PreconnectExternalDomains\AJAX\Controller as AJAXController;
use WP_Rocket\Engine\Media\PreconnectExternalDomains\Database\Table\PreconnectExternalDomains as PreconnectTable;
use WP_Rocket\Engine\Media\PreconnectExternalDomains\Frontend\{Controller as FrontController, Subscriber as FrontendSubscriber};

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
		'preconnect_external_domains_query',
		'preconnect_external_domains_context',
		'preconnect_external_domains_ajax_controller',
		'preconnect_frontend_subscriber',
		'preconnect_external_domains_table',
		'preconnect_external_domains_factory',
		'preconnect_external_domains_controller',
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
		$this->getContainer()->add( 'preconnect_external_domains_query', Query::class );
		$this->getContainer()->addShared( 'preconnect_external_domains_table', PreconnectTable::class );

		$this->getContainer()->get( 'preconnect_external_domains_table' );

		$this->getContainer()->add( 'preconnect_external_domains_context', Context::class )
			->addArgument( 'options' );

		$this->getContainer()->add( 'preconnect_external_domains_ajax_controller', AJAXController::class )
			->addArguments(
				[
					'preconnect_external_domains_query',
					'preconnect_external_domains_context',
				]
			);

		$this->getContainer()->add( 'preconnect_external_domains_controller', FrontController::class )
			->addArguments(
				[
					'preconnect_external_domains_query',
					'preconnect_external_domains_context',
				]
			);

		$this->getContainer()->addShared( 'preconnect_frontend_subscriber', FrontendSubscriber::class )
			->addArgument( 'preconnect_external_domains_controller' );

		$this->getContainer()->addShared( 'preconnect_external_domains_factory', Factory::class )
			->addArguments(
				[
					'preconnect_external_domains_query',
					'preconnect_external_domains_context',
					'preconnect_external_domains_ajax_controller',
					'preconnect_external_domains_table',
					'preconnect_external_domains_controller',
				]
			);
	}
}
