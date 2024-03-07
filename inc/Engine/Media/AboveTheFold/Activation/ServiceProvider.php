<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold\Activation;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Queries\AboveTheFold as ATFQuery;
use WP_Rocket\Engine\Media\AboveTheFold\Context\Context;
use WP_Rocket\Engine\Media\AboveTheFold\WarmUp\Controller as WarmUpController;
use WP_Rocket\Engine\Media\AboveTheFold\Jobs\Manager;

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
		'atf_query',
		'atf_context',
		'warmup_controller',
		'atf_manager',
		'atf_activation',
	];

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->add( 'atf_query', ATFQuery::class );
		$this->getContainer()->add( 'atf_context', Context::class );

		$this->getContainer()->add( 'atf_manager', Manager::class )
			->addArguments(
				[
					$this->getContainer()->get( 'atf_query' ),
					$this->getContainer()->get( 'atf_context' ),
				]
			);

		$this->getContainer()->add( 'warmup_controller', WarmUpController::class )
			->addArgument( $this->getContainer()->get( 'atf_manager' ) );

		$this->getContainer()->add( 'atf_activation', Activation::class )
			->addArguments(
				[
					$this->getContainer()->get( 'warmup_controller' ),
					$this->getContainer()->get( 'atf_manager' ),
				]
			);
	}
}
