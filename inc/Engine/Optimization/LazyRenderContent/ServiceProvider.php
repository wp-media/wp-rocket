<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\LazyRenderContent;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Optimization\LazyRenderContent\Database\Table\LazyRenderContent as LRCTable;
use WP_Rocket\Engine\Optimization\LazyRenderContent\Database\Queries\LazyRenderContent as LRCQuery;
/**
 * Service provider for LazyRenderContent
 */
class ServiceProvider extends AbstractServiceProvider {
	/**
	 * Array of services provided by this service provider
	 *
	 * @var array
	 */
	protected $provides = [
		'lrc_table',
		'lrc_query',
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
	 * Registers the option array in the container
	 *
	 * @return void
	 */
	public function register(): void {
		$this->getContainer()->addShared( 'lrc_table', LRCTable::class );
		$this->getContainer()->add( 'lrc_query', LRCQuery::class );
	}
}
