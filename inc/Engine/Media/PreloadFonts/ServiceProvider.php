<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\PreloadFonts;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Media\PreloadFonts\Database\Table\PreloadFonts as PLFTable;
use WP_Rocket\Engine\Media\PreloadFonts\Database\Queries\PreloadFonts as PLFQuery;

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
		'plf_table',
		'plf_query',
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
		$this->getContainer()->addShared( 'plf_table', PLFTable::class );
		$this->getContainer()->add( 'plf_query', PLFQuery::class );
	}
}
