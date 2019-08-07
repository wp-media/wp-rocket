<?php
namespace WP_Rocket\ServiceProvider;

use League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for Webp features.
 *
 * @since  3.4
 * @author Grégory Viguier
 */
class Webp_Subscribers extends AbstractServiceProvider {

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
		'webp_subscriber',
		'imagify_webp_subscriber',
	];

	/**
	 * Registers the subscribers in the container.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 */
	public function register() {
		$this->getContainer()->share( 'webp_subscriber', 'WP_Rocket\Subscriber\Media\Webp_Subscriber' )
			->withArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->share( 'imagify_webp_subscriber', 'WP_Rocket\Subscriber\ThirdParty\Webp\Imagify_Subscriber' )
			->withArgument( $this->getContainer()->get( 'options' ) );
	}
}
