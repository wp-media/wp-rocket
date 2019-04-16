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
		'syntaxhighlighter_subscriber',
		'elementor_subscriber',
		'bridge_subscriber',
		'ngg_subscriber',
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
		$this->getContainer()->share( 'mobile_subscriber', 'WP_Rocket\Subscriber\Third_Party\Plugins\Mobile_Subscriber' );
		$this->getContainer()->share( 'woocommerce_subscriber', 'WP_Rocket\Subscriber\Third_Party\Plugins\Ecommerce\WooCommerce_Subscriber' );
		$this->getContainer()->share( 'elementor_subscriber', 'WP_Rocket\Subscriber\Third_Party\Plugins\PageBuilder\Elementor_Subscriber' )
			->withArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->share( 'syntaxhighlighter_subscriber', 'WP_Rocket\Subscriber\Third_Party\Plugins\SyntaxHighlighter_Subscriber' );
		$this->getContainer()->share( 'bridge_subscriber', 'WP_Rocket\Subscriber\Third_Party\Themes\Bridge_Subscriber' );
		$this->getContainer()->share( 'ngg_subscriber', 'WP_Rocket\Subscriber\Third_Party\Plugins\NGG_Subscriber' );
	}
}
