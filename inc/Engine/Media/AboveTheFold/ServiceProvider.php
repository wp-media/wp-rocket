<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Media\AboveTheFold\Admin\{Controller as AdminController, Subscriber as AdminSubscriber};
use WP_Rocket\Engine\Media\AboveTheFold\AJAX\{Controller as AJAXController, Subscriber as AJAXSubscriber};
use WP_Rocket\Engine\Media\AboveTheFold\Context\Context;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Tables\AboveTheFold as ATFTable;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Queries\AboveTheFold as ATFQuery;
use WP_Rocket\Engine\Media\AboveTheFold\Frontend\{Controller as FrontController, Subscriber as FrontSubscriber};
use WP_Rocket\Engine\Media\AboveTheFold\Cron\{Controller as CronController, Subscriber as CronSubscriber};
use WP_Rocket\Engine\Media\AboveTheFold\WarmUp\{Controller as WarmUpController, Subscriber as WarmUpSubscriber};

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
		'atf_admin_controller',
		'atf_admin_subscriber',
		'atf_cron_controller',
		'atf_cron_subscriber',
		'atf_ajax_controller',
		'atf_ajax_subscriber',
		'warmup_controller',
		'warmup_subscriber',
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

		$this->getContainer()->add( 'atf_controller', FrontController::class )
			->addArguments(
				[
					$this->getContainer()->get( 'options' ),
					$this->getContainer()->get( 'atf_query' ),
					$this->getContainer()->get( 'atf_context' ),
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

		$this->getContainer()->add( 'atf_cron_controller', CronController::class )
			->addArgument( $this->getContainer()->get( 'atf_query' ) );
		$this->getContainer()->share( 'atf_cron_subscriber', CronSubscriber::class )
			->addArgument( $this->getContainer()->get( 'atf_cron_controller' ) );

		$this->getContainer()->add( 'atf_ajax_controller', AJAXController::class )
			->addArguments(
			[
				$this->getContainer()->get( 'atf_query' ),
				$this->getContainer()->get( 'atf_context' ),
			]
		);

		$this->getContainer()->share( 'atf_ajax_subscriber', AJAXSubscriber::class )
			->addArgument( $this->getContainer()->get( 'atf_ajax_controller' ) );
		$this->getContainer()->add( 'warmup_controller', WarmUpController::class )
			->addArguments(
				[
					$this->getContainer()->get( 'atf_context' ),
					$this->getContainer()->get( 'options' ),
				]
			);
		$this->getContainer()->share( 'warmup_subscriber', WarmUpSubscriber::class )
			->addArgument( $this->getContainer()->get( 'warmup_controller' ) );
	}
}
