<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Media\AboveTheFold\AJAX\Controller as AJAXController;
use WP_Rocket\Engine\Media\AboveTheFold\Context\Context;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Tables\AboveTheFold as ATFTable;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Queries\AboveTheFold as ATFQuery;
use WP_Rocket\Engine\Media\AboveTheFold\Frontend\{Controller as FrontController, Subscriber as FrontSubscriber};

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
		'atf_table',
		'atf_query',
		'atf_context',
		'atf_controller',
		'atf_subscriber',
		'atf_ajax_controller',
		'atf_factory',
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
		$this->getContainer()->addShared( 'atf_table', ATFTable::class );
		$this->getContainer()->add( 'atf_query', ATFQuery::class );
		$this->getContainer()->add( 'atf_context', Context::class );

		$this->getContainer()->get( 'atf_table' );

		$this->getContainer()->add( 'atf_controller', FrontController::class )
			->addArguments(
				[
					'options',
					'atf_query',
					'atf_context',
				]
			);

		$this->getContainer()->addShared( 'atf_subscriber', FrontSubscriber::class )
			->addArgument( 'atf_controller' );
		$this->getContainer()->add( 'atf_ajax_controller', AJAXController::class )
			->addArguments(
			[
				'atf_query',
				'atf_context',
			]
		);

		$this->getContainer()->addShared( 'atf_factory', Factory::class )
			->addArguments(
				[
					'atf_ajax_controller',
					'atf_controller',
					'atf_table',
					'atf_query',
					'atf_context',
				]
			);
	}
}
