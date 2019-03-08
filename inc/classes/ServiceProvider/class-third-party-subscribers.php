<?php
namespace WP_Rocket\ServiceProvider;

use League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for WP Rocket third party compatibility
 *
 * @since 3.3
 * @author Remy Perona
 */
class Third_Party_Subscribers extends AbstractServiceProvider {

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
		'mobile_subscriber',
		'woocommerce_subscriber',
		'nginx_subscriber',
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
		$this->getContainer()->add( 'mobile_subscriber', 'WP_Rocket\Subscriber\Third_Party\Plugins\Mobile_Subscriber' );
		$this->getContainer()->add( 'woocommerce_subscriber', 'WP_Rocket\Subscriber\Third_Party\Plugins\Ecommerce\WooCommerce_Subscriber' );
		$this->getContainer()->add( 'nginx_subscriber', 'WP_Rocket\Subscriber\Third_Party\Cache\NGINX_Subscriber' )
			->withArgument( $this->getContainer()->get( 'options' ) );
	}
}
