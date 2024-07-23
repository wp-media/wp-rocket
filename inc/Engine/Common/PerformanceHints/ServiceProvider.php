<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Common\PerformanceHints\AJAX\Subscriber as AjaxSubscriber;
use WP_Rocket\Engine\Common\PerformanceHints\Frontend\Processor as FrontendProcessor;
use WP_Rocket\Engine\Common\PerformanceHints\Frontend\Subscriber as FrontendSubscriber;
use WP_Rocket\Engine\Common\PerformanceHints\WarmUp\{
	APIClient,
	Controller as WarmUpController,
	Subscriber as WarmUpSubscriber,
	Queue
};

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
		'performance_hints_ajax_subscriber',
		'frontend_processor',
		'performance_hints_frontend_subscriber',
        'performance_hints_warmup_apiclient',
        'performance_hints_warmup_queue',
        'performance_hints_warmup_controller',
        'performance_hints_warmup_subscriber',
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

		$factories = [];

		$atf_factory = $this->getContainer()->get( 'atf_factory' );

		if ( $atf_factory->get_context()->is_allowed() ) {
			$factories[] = $atf_factory;
		}

		$this->getContainer()->addShared( 'performance_hints_ajax_subscriber', AjaxSubscriber::class )
			->addArguments(
				[
					$factories,
				]
			);

		$this->getContainer()->add( 'frontend_processor', FrontendProcessor::class )
			->addArguments(
				[
					$factories,
					$this->getContainer()->get( 'options' ),
					$this->getContainer()->get( 'atf_query' ),
				]
			);

		$this->getContainer()->addShared( 'performance_hints_frontend_subscriber', FrontendSubscriber::class )
			->addArguments(
				[
					$this->getContainer()->get( 'frontend_processor' ),
				]
			);

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
	}
}
