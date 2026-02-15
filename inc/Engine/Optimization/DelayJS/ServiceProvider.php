<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\DelayJS;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\{
	Settings,
	SiteList,
	Subscriber as AdminSubscriber
};

/**
 * Service provider for the WP Rocket Delay JS
 */
class ServiceProvider extends AbstractServiceProvider {
	/**
	 * Array of services provided by this service provider
	 *
	 * @var array
	 */
	protected $provides = [
		'delay_js_settings',
		'delay_js_admin_subscriber',
		'delay_js_html',
		'delay_js_subscriber',
		'delay_js_sitelist',
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
		$this->getContainer()->add( 'delay_js_sitelist', SiteList::class )
			->addArguments(
				[
					'dynamic_lists',
					'options',
					'options_api',
				]
			);
		$this->getContainer()->add( 'delay_js_settings', Settings::class )
			->addArgument( 'options_api' );
		$this->getContainer()->addShared( 'delay_js_admin_subscriber', AdminSubscriber::class )
			->addArguments(
				[
					'delay_js_settings',
					'delay_js_sitelist',
				]
			);
		$this->getContainer()->add( 'delay_js_html', HTML::class )
			->addArguments(
				[
					'options',
					'dynamic_lists_defaultlists_data_manager',
					'logger',
				]
			);
		$this->getContainer()->addShared( 'delay_js_subscriber', Subscriber::class )
			->addArguments(
				[
					'delay_js_html',
					rocket_direct_filesystem(),
				]
			);
	}
}
