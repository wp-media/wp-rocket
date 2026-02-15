<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints\Activation;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Common\PerformanceHints\WarmUp\{APIClient, Controller as WarmUpController, Subscriber as WarmUpSubscriber, Queue};
use WP_Rocket\Engine\Media\AboveTheFold\Context\Context as ATFContext;
use WP_Rocket\Engine\Media\AboveTheFold\Activation\ActivationFactory as ATFActivationFactory;
use WP_Rocket\Engine\Optimization\LazyRenderContent\Activation\ActivationFactory as LRCActivationFactory;
use WP_Rocket\Engine\Optimization\LazyRenderContent\Context\Context as LRCContext;
use WP_Rocket\Engine\Media\PreconnectExternalDomains\Context\Context as PreconnectContext;

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
		'lrc_context',
		'lrc_activation_factory',
		'preconnect_external_domains_context',
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
					'atf_context',
				]
			);

		$this->getContainer()->add( 'lrc_context', LRCContext::class );

		$this->getContainer()->addShared( 'lrc_activation_factory', LRCActivationFactory::class )
			->addArguments(
				[
					'lrc_context',
				]
			);

		$this->getContainer()->add( 'preconnect_external_domains_context', PreconnectContext::class );

		$factories = [];

		$atf_activation_factory = $this->getContainer()->get( 'atf_activation_factory' );

		if ( $atf_activation_factory->get_context()->is_allowed() ) {
			$factories[] = $atf_activation_factory;
		}

		$lrc_activation_factory = $this->getContainer()->get( 'lrc_activation_factory' );

		if ( $lrc_activation_factory->get_context()->is_allowed() ) {
			$factories[] = $lrc_activation_factory;
		}

		$this->getContainer()->add( 'performance_hints_warmup_apiclient', APIClient::class )
			->addArgument( 'options' );

		$this->getContainer()->add( 'performance_hints_warmup_queue', Queue::class );

		$this->getContainer()->add( 'performance_hints_warmup_controller', WarmUpController::class )
			->addArguments(
				[
					$factories,
					'options',
					'performance_hints_warmup_apiclient',
					'user',
					'performance_hints_warmup_queue',
				]
			);

		$this->getContainer()->addShared( 'performance_hints_warmup_subscriber', WarmUpSubscriber::class )
			->addArgument( 'performance_hints_warmup_controller' );

		$this->getContainer()->add( 'performance_hints_activation', Activation::class )
			->addArguments(
				[
					'performance_hints_warmup_controller',
					$factories,
				]
			);
	}
}
