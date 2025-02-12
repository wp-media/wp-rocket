<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\PreloadFonts;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Media\PreloadFonts\Context\Context;
use WP_Rocket\Engine\Common\PerformanceHints\Database\Table\AbstractTable as Table;
use WP_Rocket\Engine\Common\PerformanceHints\Database\Queries\AbstractQueries as Queries;
use WP_Rocket\Engine\Media\PreloadFonts\Frontend\Controller as FrontController;
use WP_Rocket\Engine\Common\PerformanceHints\AJAX\AJAXControllerTrait;



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
		'pf_table',
		'pf_query',
		'pf_ajax_controller',
		'pf_controller',
		'pf_factory',
		'pf_context',
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
		$this->getContainer()->addShared( 'pf_table', Table::class );
		$this->getContainer()->add( 'pf_query', Queries::class );
		$this->getContainer()->add( 'pf_context', Context::class );

		$this->getContainer()->get( 'pf_table' );

		$this->getContainer()->add( 'pf_controller', FrontController::class )
		->addArguments(
			[
				$this->getContainer()->get( 'options' ),
				$this->getContainer()->get( 'pf_query' ),
				$this->getContainer()->get( 'pf_context' ),
			]
		);

		$this->getContainer()->add( 'atf_ajax_controller', AJAXControllerTrait::class )
			->addArguments(
			[
				$this->getContainer()->get( 'pf_query' ),
				$this->getContainer()->get( 'pf_context' ),
			]
		);

		$this->getContainer()->addShared( 'pf_factory', Factory::class )
			->addArguments(
				[
					$this->getContainer()->get( 'pf_ajax_controller' ),
					$this->getContainer()->get( 'pf_controller' ),
					$this->getContainer()->get( 'pf_table' ),
					$this->getContainer()->get( 'pf_query' ),
					$this->getContainer()->get( 'pf_context' ),
				]
			);
	}
}
