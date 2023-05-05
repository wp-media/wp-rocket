<?php
namespace WP_Rocket\Engine\Optimization\DelayJS;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\Settings;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\SiteList;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\Subscriber as AdminSubscriber;
use WP_Rocket\Logger\Logger;

/**
 * Service provider for the WP Rocket Delay JS
 *
 * @since  3.7
 */
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
		'delay_js_settings',
		'delay_js_admin_subscriber',
		'delay_js_html',
		'delay_js_subscriber',
		'delay_js_sitelist',
	];

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->add( 'delay_js_sitelist', SiteList::class )
			->addArgument( $this->getContainer()->get( 'dynamic_lists' ) )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addArgument( $this->getContainer()->get( 'options_api' ) );
		$this->getContainer()->add( 'delay_js_settings', Settings::class )
			->addArgument( $this->getContainer()->get( 'options_api' ) );
		$this->getContainer()->share( 'delay_js_admin_subscriber', AdminSubscriber::class )
			->addArgument( $this->getContainer()->get( 'delay_js_settings' ) )
			->addArgument( $this->getContainer()->get( 'delay_js_sitelist' ) )
			->addTag( 'admin_subscriber' );
		$this->getContainer()->add( 'delay_js_html', HTML::class )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addArgument( $this->getContainer()->get( 'dynamic_lists_defaultlists_data_manager' ) )
			->addArgument( new Logger() );
		$this->getContainer()->share( 'delay_js_subscriber', Subscriber::class )
			->addArgument( $this->getContainer()->get( 'delay_js_html' ) )
			->addArgument( rocket_direct_filesystem() )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addTag( 'front_subscriber' );
	}
}
