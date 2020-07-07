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
		'beaverbuilder_subscriber',
		'amp_subscriber',
		'litespeed_subscriber',
		'pressable_subscriber',
		'simple_custom_css',
		'cloudways',
		'wpengine',
		'spinupwp',
		'pdfembedder',
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
		$this->getContainer()->share( 'elementor_subscriber', 'WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor' )
			->withArgument( $options );
		$this->getContainer()->share( 'woocommerce_subscriber', 'WP_Rocket\ThirdParty\Plugins\Ecommerce\WooCommerceSubscriber' );
		$this->getContainer()->share( 'syntaxhighlighter_subscriber', 'WP_Rocket\Subscriber\Third_Party\Plugins\SyntaxHighlighter_Subscriber' );
		$this->getContainer()->share( 'bridge_subscriber', 'WP_Rocket\ThirdParty\Themes\Bridge' )
			->withArgument( $options );
		$this->getContainer()->share( 'ngg_subscriber', 'WP_Rocket\Subscriber\Third_Party\Plugins\NGG_Subscriber' );
		$this->getContainer()->share( 'smush_subscriber', 'WP_Rocket\ThirdParty\Plugins\Smush' )
			->withArgument( $this->getContainer()->get( 'options_api' ) )
			->withArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->share( 'imagify_webp_subscriber', 'WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber' )
			->withArgument( $options );
		$this->getContainer()->share( 'shortpixel_webp_subscriber', 'WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\ShortPixel_Subscriber' )
			->withArgument( $options );
		$this->getContainer()->share( 'ewww_webp_subscriber', 'WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\EWWW_Subscriber' )
			->withArgument( $options );
		$this->getContainer()->share( 'optimus_webp_subscriber', 'WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Optimus_Subscriber' );
		$this->getContainer()->share( 'bigcommerce_subscriber', 'WP_Rocket\Subscriber\Third_Party\Plugins\Ecommerce\BigCommerce_Subscriber' );
		$this->getContainer()->share( 'beaverbuilder_subscriber', 'WP_Rocket\ThirdParty\Plugins\PageBuilder\BeaverBuilder' );
		$this->getContainer()->share( 'amp_subscriber', 'WP_Rocket\ThirdParty\Plugins\Optimization\AMP' )
			->withArgument( $options )
			->withArgument( $this->getContainer()->get( 'cdn_subscriber' ) );
		$this->getContainer()->share( 'pressable_subscriber', 'WP_Rocket\ThirdParty\Hostings\Pressable' )
			->withArgument( $this->getContainer()->get( 'admin_cache_subscriber' ) );
		$this->getContainer()->share( 'litespeed_subscriber', 'WP_Rocket\Subscriber\Third_Party\Hostings\Litespeed_Subscriber' );
		$this->getContainer()->share( 'simple_custom_css', 'WP_Rocket\ThirdParty\Plugins\SimpleCustomCss' )
			->withArgument( WP_ROCKET_CACHE_BUSTING_PATH )
			->withArgument( WP_ROCKET_CACHE_BUSTING_URL );
		$this->getContainer()->share( 'cloudways', 'WP_Rocket\ThirdParty\Hostings\Cloudways' );
		$this->getContainer()->share( 'wpengine', 'WP_Rocket\ThirdParty\Hostings\WPEngine' );
		$this->getContainer()->share( 'spinupwp', 'WP_Rocket\ThirdParty\Hostings\SpinUpWP' );
		$this->getContainer()->share( 'pdfembedder', 'WP_Rocket\ThirdParty\Plugins\PDFEmbedder' );
	}
}
