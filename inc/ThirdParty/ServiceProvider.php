<?php
namespace WP_Rocket\ThirdParty;

use League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for WP Rocket third party compatibility
 *
 * @since 3.3
 * @author Remy Perona
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
		'mobile_subscriber',
		'woocommerce_subscriber',
		'syntaxhighlighter_subscriber',
		'elementor_subscriber',
		'bridge_subscriber',
		'ngg_subscriber',
		'smush_subscriber',
		'imagify_webp_subscriber',
		'shortpixel_webp_subscriber',
		'ewww_webp_subscriber',
		'optimus_webp_subscriber',
		'bigcommerce_subscriber',
		'amp_subscriber',
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
		$options = $this->getContainer()->get( 'options' );

		$this->getContainer()->share( 'mobile_subscriber', 'WP_Rocket\Subscriber\Third_Party\Plugins\Mobile_Subscriber' );
		$this->getContainer()->share( 'woocommerce_subscriber', 'WP_Rocket\ThirdParty\Plugins\Ecommerce\WooCommerceSubscriber' );
		$this->getContainer()->share( 'elementor_subscriber', 'WP_Rocket\Subscriber\Third_Party\Plugins\PageBuilder\Elementor_Subscriber' )
			->withArgument( $options );
		$this->getContainer()->share( 'syntaxhighlighter_subscriber', 'WP_Rocket\Subscriber\Third_Party\Plugins\SyntaxHighlighter_Subscriber' );
		$this->getContainer()->share( 'bridge_subscriber', 'WP_Rocket\Subscriber\Third_Party\Themes\Bridge_Subscriber' )
			->withArgument( $options );
		$this->getContainer()->share( 'ngg_subscriber', 'WP_Rocket\Subscriber\Third_Party\Plugins\NGG_Subscriber' );
		$this->getContainer()->share( 'smush_subscriber', 'WP_Rocket\Subscriber\Third_Party\Plugins\Smush_Subscriber' );
		$this->getContainer()->share( 'imagify_webp_subscriber', 'WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber' )
			->withArgument( $options );
		$this->getContainer()->share( 'shortpixel_webp_subscriber', 'WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\ShortPixel_Subscriber' )
			->withArgument( $options );
		$this->getContainer()->share( 'ewww_webp_subscriber', 'WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\EWWW_Subscriber' )
			->withArgument( $options );
		$this->getContainer()->share( 'optimus_webp_subscriber', 'WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Optimus_Subscriber' );
		$this->getContainer()->share( 'bigcommerce_subscriber', 'WP_Rocket\Subscriber\Third_Party\Plugins\Ecommerce\BigCommerce_Subscriber' );
		$this->getContainer()->share( 'amp_subscriber', 'WP_Rocket\ThirdParty\Plugins\Optimization\AMP' )
			->withArgument( $options );
	}
}
