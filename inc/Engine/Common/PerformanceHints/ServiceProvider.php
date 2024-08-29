<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Common\PerformanceHints\Admin\{
	Controller as AdminController,
	Subscriber as AdminSubscriber,
	AdminBar,
	Clean,
	Notices
};
use WP_Rocket\Engine\Common\PerformanceHints\AJAX\{Processor as AjaxProcessor, Subscriber as AjaxSubscriber};
use WP_Rocket\Engine\Common\PerformanceHints\Frontend\{Processor as FrontendProcessor, Subscriber as FrontendSubscriber };
use WP_Rocket\Engine\Common\PerformanceHints\Cron\{Controller as CronController, Subscriber as CronSubscriber};
use WP_Rocket\Engine\Common\PerformanceHints\WarmUp\{
	APIClient,
	Controller as WarmUpController,
	Subscriber as WarmUpSubscriber,
	Queue
};
use WP_Rocket\Engine\Optimization\LazyRenderContent\Context\Context as LRCContext;

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
		'ajax_processor',
		'performance_hints_ajax_subscriber',
		'frontend_processor',
		'performance_hints_frontend_subscriber',
		'performance_hints_admin_subscriber',
		'performance_hints_admin_controller',
		'performance_hints_cron_subscriber',
		'cron_controller',
		'performance_hints_warmup_apiclient',
		'performance_hints_warmup_queue',
		'performance_hints_warmup_controller',
		'performance_hints_warmup_subscriber',
		'performance_hints_admin_bar',
		'performance_hints_clean',
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

		$factory_array = [
			$this->getContainer()->get( 'atf_factory' ),
			$this->getContainer()->get( 'lrc_factory' ),
		];

		foreach ( $factory_array as $factory ) {
			if ( ! $factory->get_context()->is_allowed() ) {
				continue;
			}

			$factories[] = $factory;
		}

		$this->getContainer()->addShared( 'ajax_processor', AjaxProcessor::class )
			->addArguments(
				[
					$factories,
				]
			);

		$this->getContainer()->addShared( 'performance_hints_ajax_subscriber', AjaxSubscriber::class )
			->addArgument( $this->getContainer()->get( 'ajax_processor' ) );

		$this->getContainer()->add( 'frontend_processor', FrontendProcessor::class )
			->addArguments(
				[
					$factories,
					$this->getContainer()->get( 'options' ),
				]
			);

		$this->getContainer()->addShared( 'performance_hints_frontend_subscriber', FrontendSubscriber::class )
			->addArguments(
				[
					$this->getContainer()->get( 'frontend_processor' ),
				]
			);

		$this->getContainer()->add( 'performance_hints_admin_controller', AdminController::class )
			->addArguments(
				[
					$factory_array,
				]
			);

		$this->getContainer()->add( 'performance_hints_notices', Notices::class )
			->addArguments(
				[
					$factories,
				]
			);

		$this->getContainer()->add( 'performance_hints_admin_bar', Adminbar::class )
			->addArguments(
				[
					$factories,
					$this->getContainer()->get( 'template_path' ) . '/settings',
				]
			);

		$this->getContainer()->add( 'performance_hints_clean', Clean::class );

		$this->getContainer()->addShared( 'performance_hints_admin_subscriber', AdminSubscriber::class )
			->addArguments(
				[
					$this->getContainer()->get( 'performance_hints_admin_controller' ),
					$this->getContainer()->get( 'performance_hints_admin_bar' ),
					$this->getContainer()->get( 'performance_hints_clean' ),
					$this->getContainer()->get( 'performance_hints_notices' ),
				]
			);
		$this->getContainer()->add( 'cron_controller', CronController::class )
			->addArgument( $factory_array );

		$this->getContainer()->addShared( 'performance_hints_cron_subscriber', CronSubscriber::class )
			->addArgument( $this->getContainer()->get( 'cron_controller' ) );

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
