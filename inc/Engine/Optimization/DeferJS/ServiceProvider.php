<?php
namespace WP_Rocket\Engine\Optimization\DeferJS;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for the WP Rocket Defer JS
 */
class ServiceProvider extends AbstractServiceProvider {
	/**
	 * Array of services provided by this service provider
	 *
	 * @var array
	 */
	protected $provides = [
		'defer_js',
		'defer_js_admin_subscriber',
		'defer_js_subscriber',
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
		$this->getContainer()->add( 'defer_js', DeferJS::class )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addArgument( $this->getContainer()->get( 'dynamic_lists_defaultlists_data_manager' ) );
		$this->getContainer()->addShared( 'defer_js_admin_subscriber', AdminSubscriber::class )
			->addArgument( $this->getContainer()->get( 'defer_js' ) )
			->addTag( 'admin_subscriber' );
		$this->getContainer()->addShared( 'defer_js_subscriber', Subscriber::class )
			->addArgument( $this->getContainer()->get( 'defer_js' ) )
			->addTag( 'front_subscriber' );
	}
}
