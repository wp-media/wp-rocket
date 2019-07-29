<?php
namespace WP_Rocket\ServiceProvider;

use League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for WP Rocket features common for admin and front
 *
 * @since 3.3
 * @author Remy Perona
 */
class Common_Subscribers extends AbstractServiceProvider {

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
		'heartbeat_subscriber',
		'db_optimization_subscriber',
		'critical_css_generation',
		'critical_css',
		'critical_css_subscriber',
		'cache_dir_size_check_subscriber',
		'capabilities_subscriber',
	];

	/**
	 * Registers the subscribers in the container
	 *
	 * @since 3.3
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->share( 'heartbeat_subscriber', 'WP_Rocket\Subscriber\Heartbeat_Subscriber' )
			->withArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->share( 'db_optimization_subscriber', 'WP_Rocket\Subscriber\Admin\Database\Optimization_Subscriber' )
			->withArgument( $this->getContainer()->get( 'db_optimization' ) )
			->withArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->add( 'critical_css_generation', 'WP_Rocket\Optimization\CSS\Critical_CSS_Generation' );
		$this->getContainer()->add( 'critical_css', 'WP_Rocket\Optimization\CSS\Critical_CSS' )
			->withArgument( $this->getContainer()->get( 'critical_css_generation' ) );
		$this->getContainer()->share( 'critical_css_subscriber', 'WP_Rocket\Subscriber\Optimization\Critical_CSS_Subscriber' )
			->withArgument( $this->getContainer()->get( 'critical_css' ) )
			->withArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->share( 'cache_dir_size_check_subscriber', 'WP_Rocket\Subscriber\Tools\Cache_Dir_Size_Check_Subscriber' );
		$this->getContainer()->share( 'automatic_cache_purge_subscriber', 'WP_Rocket\Subscriber\Optimization\Automatic_Cache_Purge_Subscriber' )
			->withArgument(
				[
					'options'    => $this->getContainer()->get( 'options' ),
					'cache_path' => WP_ROCKET_CACHE_PATH,
				]
			);
		$this->getContainer()->share( 'capabilities_subscriber', 'WP_Rocket\Subscriber\Plugin\Capabilities_Subscriber' );
	}
}
