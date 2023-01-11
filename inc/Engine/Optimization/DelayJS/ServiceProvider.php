<?php
namespace WP_Rocket\Engine\Optimization\DelayJS;

use WP_Rocket\AbstractServiceProvider;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\Settings;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\Subscriber as AdminSubscriber;

/**
 * Service provider for the WP Rocket Delay JS
 *
 * @since  3.7
 */
class ServiceProvider extends AbstractServiceProvider {

	public function get_front_subscribers(): array
	{
		return [
			$this->getInternal('delay_js_subscriber')
		];
	}

	public function get_admin_subscribers(): array
	{
		return [
			$this->getInternal('delay_js_admin_subscriber'),
		];
	}

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$this->add( 'delay_js_settings', Settings::class );
		$this->share( 'delay_js_admin_subscriber', AdminSubscriber::class )
			->addArgument( $this->getInternal( 'delay_js_settings' ) )
			->addTag( 'admin_subscriber' );
		$this->add( 'delay_js_html', HTML::class )
			->addArgument( $this->getInternal( 'options' ) )
			->addArgument( $this->getInternal( 'dynamic_lists_data_manager' ) );
		$this->share( 'delay_js_subscriber', Subscriber::class )
			->addArgument( $this->getInternal( 'delay_js_html' ) )
			->addArgument( rocket_direct_filesystem() )
			->addArgument( $this->getInternal( 'options' ) )
			->addTag( 'front_subscriber' );
	}
}
