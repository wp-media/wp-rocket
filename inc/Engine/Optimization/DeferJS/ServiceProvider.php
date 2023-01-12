<?php
namespace WP_Rocket\Engine\Optimization\DeferJS;

use WP_Rocket\AbstractServiceProvider;

/**
 * Service provider for the WP Rocket Defer JS
 *
 * @since 3.8
 */
class ServiceProvider extends AbstractServiceProvider {

	public function get_front_subscribers(): array
	{
		return [
			$this->generate_container_id('defer_js_subscriber')
		];
	}

	public function get_admin_subscribers(): array
	{
		return [
			$this->generate_container_id('defer_js_admin_subscriber'),
		];
	}

	public function declare()
	{
		$this->register_service('defer_js', function ($id) {
			$this->add( $id, DeferJS::class )
				->addArgument( $this->get_internal( 'options' ) )
				->addArgument( $this->get_internal( 'dynamic_lists_data_manager' ) );
		});

		$this->register_service('defer_js_admin_subscriber', function ($id) {
			$this->share( $id, AdminSubscriber::class )
				->addArgument( $this->get_internal( 'defer_js' ) )
				->addTag( 'admin_subscriber' );
		});

		$this->register_service('defer_js_subscriber', function ($id) {
			$this->share( $id, Subscriber::class )
				->addArgument( $this->get_internal( 'defer_js' ) )
				->addTag( 'front_subscriber' );
		});
	}
}
