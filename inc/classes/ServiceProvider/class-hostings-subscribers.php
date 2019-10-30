<?php
namespace WP_Rocket\ServiceProvider;

use League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for WP Rocket third party hostings compatibility
 *
 * @since 3.3
 * @author Remy Perona
 */
class Hostings_Subscribers extends AbstractServiceProvider {

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
		'pressable_subscriber',
		'litespeed_subscriber',
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
		$this->getContainer()->share( 'pressable_subscriber', 'WP_Rocket\Subscriber\Third_Party\Hostings\Pressable_Subscriber' );
		$this->getContainer()->share( 'litespeed_subscriber', 'WP_Rocket\Subscriber\Third_Party\Hostings\Litespeed_Subscriber' );
	}
}
