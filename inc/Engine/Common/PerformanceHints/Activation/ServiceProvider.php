<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints\Activation;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Common\PerformanceHints\WarmUp\{APIClient, Controller as WarmUpController, Subscriber as WarmUpSubscriber, Queue};
use WP_Rocket\Engine\Media\AboveTheFold\Context\Context as ATFContext;
use WP_Rocket\Engine\Media\AboveTheFold\Activation\ActivationFactory as ATFActivationFactory;
use WP_Rocket\Engine\Optimization\LazyRenderContent\Activation\ActivationFactory as LCRActivationFactory;

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
		'performance_hints_warmup_apiclient',
		'performance_hints_warmup_queue',
		'performance_hints_warmup_controller',
		'performance_hints_activation',
		'performance_hints_warmup_subscriber',
		'atf_context',
		'atf_activation_factory',
		'lcr_activation_factory',
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
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register(): void {

		$this->getContainer()->add( 'atf_context', ATFContext::class );

		$this->getContainer()->addShared( 'atf_activation_factory', ATFActivationFactory::class )
			->addArguments(
				[
					$this->getContainer()->get( 'atf_context' ),
				]
			);
		$this->getContainer()->addShared( 'lcr_activation_factory', LCRActivationFactory::class )
			->addArguments(
				[
					$this->getContainer()->get( 'lcr_context' ),
				]
			);

		$factories = [];

		$atf_activation_factory = $this->getContainer()->get( 'atf_activation_factory' );

		if ( $atf_activation_factory->get_context()->is_allowed() ) {
			$factories[] = $atf_activation_factory;
		}

		$lcr_activation_factory = $this->getContainer()->get( 'lcr_activation_factory' );

		if ( $lcr_activation_factory->get_context()->is_allowed() ) {
			$factories[] = $lcr_activation_factory;
		}

		$this->getContainer()->add( 'performance_hints_warmup_apiclient', APIClient::class )
			->addArgument( $this->getContainer()->get( 'options' ) );

		$this->getContainer()->add( 'performance_hints_warmup_queue', Queue::class );

		$this->getContainer()->add( 'performance_hints_warmup_controller', WarmUpController::class )
			->addArguments(
				[
					$factories,
					$this->getContainer()->get( 'options' ),
					$this->getContainer()->get( 'performance_hints_warmup_apiclient' ),
					$this->getContainer()->get( 'user' ),
					$this->getContainer()->get( 'performance_hints_warmup_queue' ),
				]
			);

		$this->getContainer()->addShared( 'performance_hints_warmup_subscriber', WarmUpSubscriber::class )
			->addArgument( $this->getContainer()->get( 'performance_hints_warmup_controller' ) );

		$this->getContainer()->add( 'performance_hints_activation', Activation::class )
			->addArguments(
				[
					$this->getContainer()->get( 'performance_hints_warmup_controller' ),
					$factories,
				]
			);
	}
}
