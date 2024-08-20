<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\LazyRenderContent;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Optimization\LazyRenderContent\Context\Context;
use WP_Rocket\Engine\Optimization\LazyRenderContent\Database\Table\LazyRenderContent as LRCTable;
use WP_Rocket\Engine\Optimization\LazyRenderContent\Database\Queries\LazyRenderContent as LRCQuery;
use WP_Rocket\Engine\Optimization\LazyRenderContent\AJAX\Controller as AJAXController;

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
		'lrc_context',
		'lrc_factory',
		'lrc_table',
		'lrc_query',
		'lrc_controller',
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
		$this->getContainer()->add( 'lrc_context', Context::class );

		$this->getContainer()->addShared( 'lrc_table', LRCTable::class );

		$this->getContainer()->add( 'lrc_query', LRCQuery::class );

		$this->getContainer()->addShared( 'lrc_factory', Factory::class )
			->addArguments(
				[
					$this->getContainer()->get( 'lrc_context' ),
					$this->getContainer()->get( 'lrc_table' ),
					$this->getContainer()->get( 'lrc_query' ),
				]
			);

		$this->getContainer()->addShared( 'lrc_table', LRCTable::class );

		$this->getContainer()->add( 'lrc_query', LRCQuery::class );

		$this->getContainer()->add( 'atf_ajax_controller', AJAXController::class )
			->addArguments(
				[
					$this->getContainer()->get( 'lrc_query' ),
					$this->getContainer()->get( 'lrc_context' ),
				]
			);
	}
}
