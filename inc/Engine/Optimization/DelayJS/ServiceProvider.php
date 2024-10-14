<?php
namespace WP_Rocket\Engine\Optimization\DelayJS;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\{
	Settings,
	SiteList,
	Subscriber as AdminSubscriber
};
use WP_Rocket\Logger\Logger;

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
			->addArgument( $this->getContainer()->get( 'dynamic_lists' ) )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addArgument( $this->getContainer()->get( 'options_api' ) );
		$this->getContainer()->add( 'delay_js_settings', Settings::class )
			->addArgument( $this->getContainer()->get( 'options_api' ) );
		$this->getContainer()->addShared( 'delay_js_admin_subscriber', AdminSubscriber::class )
			->addArgument( $this->getContainer()->get( 'delay_js_settings' ) )
			->addArgument( $this->getContainer()->get( 'delay_js_sitelist' ) );
		$this->getContainer()->add( 'delay_js_html', HTML::class )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addArgument( $this->getContainer()->get( 'dynamic_lists_defaultlists_data_manager' ) )
			->addArgument( new Logger() );
		$this->getContainer()->addShared( 'delay_js_subscriber', Subscriber::class )
			->addArgument( $this->getContainer()->get( 'delay_js_html' ) )
			->addArgument( rocket_direct_filesystem() );
	}
}
