<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Media\AboveTheFold\Context\Context;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Tables\AboveTheFold as ATFTable;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Queries\AboveTheFold as ATFQuery;
use WP_Rocket\Engine\Media\AboveTheFold\Admin\{Controller as AdminController, Subscriber as AdminSubscriber};
use WP_Rocket\Engine\Media\AboveTheFold\Frontend\{Controller as FrontController, Subscriber as FrontSubscriber};
use WP_Rocket\Engine\Media\AboveTheFold\Jobs\{Manager, Factory};

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
		'atf_manager',
		'atf_factory',
		'atf_admin_controller',
		'atf_admin_subscriber',
	];

	/**
	 * Registers the classes in the container
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->share( 'atf_table', ATFTable::class );
		$this->getContainer()->add( 'atf_query', ATFQuery::class );
		$this->getContainer()->add( 'atf_context', Context::class );

		$this->getContainer()->get( 'atf_table' );

		$this->getContainer()->add( 'atf_manager', Manager::class )
			->addArguments(
				[
					$this->getContainer()->get( 'atf_query' ),
					$this->getContainer()->get( 'atf_context' ),
				]
				);

		$this->getContainer()->share( 'atf_factory', Factory::class )
			->addArguments(
				[
					$this->getContainer()->get( 'atf_manager' ),
					$this->getContainer()->get( 'atf_table' ),
				]
				);

		$this->getContainer()->add( 'atf_controller', FrontController::class )
			->addArguments(
				[
					$this->getContainer()->get( 'options' ),
					$this->getContainer()->get( 'atf_query' ),
					$this->getContainer()->get( 'atf_context' ),
					$this->getContainer()->get( 'atf_manager' ),
				]
				);

		$this->getContainer()->share( 'atf_subscriber', FrontSubscriber::class )
			->addArgument( $this->getContainer()->get( 'atf_controller' ) );
		$this->getContainer()->add( 'atf_admin_controller', AdminController::class )
			->addArgument( $this->getContainer()->get( 'atf_table' ) )
			->addArgument( $this->getContainer()->get( 'atf_query' ) )
			->addArgument( $this->getContainer()->get( 'atf_context' ) );
		$this->getContainer()->share( 'atf_admin_subscriber', AdminSubscriber::class )
			->addArgument( $this->getContainer()->get( 'atf_admin_controller' ) );
	}
}
