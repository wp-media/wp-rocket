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
	];

	/**
	 * Return IDs from license subscribers.
	 *
	 * @return string[]
	 */
	public function get_license_subscribers(): array {
		return [
			'delay_js_subscriber',
		];
	}

	/**
	 * Returns common subscribers.
	 *
	 * @return string[]
	 */
	public function get_common_subscribers(): array {
		return [
			'delay_js_admin_subscriber',
		];
	}

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->add( 'delay_js_settings', Settings::class );
		$this->getContainer()->share( 'delay_js_admin_subscriber', AdminSubscriber::class )
			->addArgument( $this->getContainer()->get( 'delay_js_settings' ) )
			->addTag( 'admin_subscriber' );
		$this->getContainer()->add( 'delay_js_html', HTML::class )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addArgument( $this->getContainer()->get( 'dynamic_lists_data_manager' ) );
		$this->getContainer()->share( 'delay_js_subscriber', Subscriber::class )
			->addArgument( $this->getContainer()->get( 'delay_js_html' ) )
			->addArgument( rocket_direct_filesystem() )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addTag( 'front_subscriber' );
	}
}
