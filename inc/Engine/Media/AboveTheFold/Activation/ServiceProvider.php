<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold\Activation;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Media\AboveTheFold\Context\Context;
use WP_Rocket\Engine\Media\AboveTheFold\WarmUp\Controller as WarmUpController;

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
		'atf_context',
		'warmup_controller',
		'atf_activation',
	];

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->add( 'atf_context', Context::class );

		$this->getContainer()->add( 'warmup_controller', WarmUpController::class )
			->addArguments(
				[
					$this->getContainer()->get( 'atf_context' ),
					$this->getContainer()->get( 'options' ),
				]
			);

		$this->getContainer()->add( 'atf_activation', Activation::class )
			->addArguments(
				[
					$this->getContainer()->get( 'warmup_controller' ),
					$this->getContainer()->get( 'atf_context' ),
				]
			);
	}
}
