<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints;

use WP_Rocket\Buffer\{Config, Tests};
use WP_Rocket\Dependencies\League\Container\Argument\Literal\{ArrayArgument, StringArgument};
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
		'config',
		'tests',
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
		'performance_hints_notices',
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
			$this->getContainer()->get( 'preload_fonts_factory' ),
			$this->getContainer()->get( 'preconnect_external_domains_factory' ),
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
			->addArgument( 'ajax_processor' );

		$this->getContainer()->add( 'frontend_processor', FrontendProcessor::class )
			->addArguments(
				[
					$factories,
					'options',
				]
			);

		$this->getContainer()->add( 'config', Config::class )
			->addArgument(
				new ArrayArgument(
					[
						'config_dir_path' => rocket_get_constant( 'WP_ROCKET_CONFIG_PATH', '' ),
					]
				)
			);
		$this->getContainer()->add( 'tests', Tests::class )
			->addArgument( 'config' );

		$this->getContainer()->addShared( 'performance_hints_frontend_subscriber', FrontendSubscriber::class )
			->addArguments(
				[
					'frontend_processor',
					'tests',
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
					new StringArgument( $this->getContainer()->get( 'template_path' ) . '/settings' ),
				]
			);
		$this->getContainer()->add( 'performance_hints_clean', Clean::class );
		$this->getContainer()->addShared( 'performance_hints_admin_subscriber', AdminSubscriber::class )
			->addArguments(
				[
					'performance_hints_admin_controller',
					'performance_hints_admin_bar',
					'performance_hints_clean',
					'performance_hints_notices',
				]
			);
		$this->getContainer()->add( 'cron_controller', CronController::class )
			->addArgument( $factory_array );
		$this->getContainer()->addShared( 'performance_hints_cron_subscriber', CronSubscriber::class )
			->addArgument( 'cron_controller' );
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
	}
}
