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

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$this->add( 'defer_js', DeferJS::class )
			->addArgument( $this->get_internal( 'options' ) )
			->addArgument( $this->get_internal( 'dynamic_lists_data_manager' ) );
		$this->share( 'defer_js_admin_subscriber', AdminSubscriber::class )
			->addArgument( $this->get_internal( 'defer_js' ) )
			->addTag( 'admin_subscriber' );
		$this->share( 'defer_js_subscriber', Subscriber::class )
			->addArgument( $this->get_internal( 'defer_js' ) )
			->addTag( 'front_subscriber' );
	}
}
