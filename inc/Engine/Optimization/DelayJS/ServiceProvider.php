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
			$this->generate_container_id('delay_js_subscriber')
		];
	}

	public function get_admin_subscribers(): array
	{
		return [
			$this->generate_container_id('delay_js_admin_subscriber'),
		];
	}

	public function declare()
	{
		$this->register_service('delay_js_settings', function($id) {
			$this->add( $id, Settings::class );
		});

		$this->register_service('delay_js_admin_subscriber', function($id) {
			$this->share( $id, AdminSubscriber::class )
				->addArgument( $this->get_internal( 'delay_js_settings' ) )
				->addTag( 'admin_subscriber' );
		});

		$this->register_service('delay_js_html', function($id) {
			$this->add( $id, HTML::class )
				->addArgument( $this->get_internal( 'options' ) )
				->addArgument( $this->get_internal( 'dynamic_lists_data_manager' ) );
		});

		$this->register_service('delay_js_subscriber', function($id) {
			$this->share( $id, Subscriber::class )
				->addArgument( $this->get_internal( 'delay_js_html' ) )
				->addArgument( rocket_direct_filesystem() )
				->addArgument( $this->get_internal( 'options' ) )
				->addTag( 'front_subscriber' );
		});
	}
}
