<?php
namespace WP_Rocket\ThirdParty;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for WP Rocket third party compatibility
 *
 * @since 3.3
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
		'avada_subscriber',
		'ngg_subscriber',
		'smush_subscriber',
		'imagify_webp_subscriber',
		'shortpixel_webp_subscriber',
		'ewww_webp_subscriber',
		'optimus_webp_subscriber',
		'bigcommerce_subscriber',
		'beaverbuilder_subscriber',
		'amp_subscriber',
		'simple_custom_css',
		'pdfembedder',
		'divi',
		'mod_pagespeed',
		'adthrive',
		'autoptimize',
		'wp-meteor',
		'revolution_slider_subscriber',
		'wordfence_subscriber',
		'ezoic',
		'pwa',
		'flatsome',
		'convertplug',
		'inline_related_posts',
	];

	/**
	 * Registers the subscribers in the container
	 *
	 * @since 3.3
	 *
	 * @return void
	 */
	public function register() {
		$options = $this->getContainer()->get( 'options' );

		$this->getContainer()
			->share( 'mobile_subscriber', 'WP_Rocket\Subscriber\Third_Party\Plugins\Mobile_Subscriber' )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'elementor_subscriber', 'WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor' )
			->addArgument( $options )
			->addArgument( rocket_direct_filesystem() )
			->addArgument( $this->getContainer()->get( 'delay_js_html' ) )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'woocommerce_subscriber', 'WP_Rocket\ThirdParty\Plugins\Ecommerce\WooCommerceSubscriber' )
			->addArgument( $this->getContainer()->get( 'delay_js_html' ) )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'syntaxhighlighter_subscriber', 'WP_Rocket\Subscriber\Third_Party\Plugins\SyntaxHighlighter_Subscriber' )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'bridge_subscriber', 'WP_Rocket\ThirdParty\Themes\Bridge' )
			->addArgument( $options )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'divi', 'WP_Rocket\ThirdParty\Themes\Divi' )
			->addArgument( $this->getContainer()->get( 'options_api' ) )
			->addArgument( $options )
			->addArgument( $this->getContainer()->get( 'delay_js_html' ) )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'avada_subscriber', 'WP_Rocket\ThirdParty\Themes\Avada' )
			->addArgument( $options )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'ngg_subscriber', 'WP_Rocket\Subscriber\Third_Party\Plugins\NGG_Subscriber' )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'smush_subscriber', 'WP_Rocket\ThirdParty\Plugins\Smush' )
			->addArgument( $this->getContainer()->get( 'options_api' ) )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'imagify_webp_subscriber', 'WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber' )
			->addArgument( $options )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'shortpixel_webp_subscriber', 'WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\ShortPixel_Subscriber' )
			->addArgument( $options )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'ewww_webp_subscriber', 'WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\EWWW_Subscriber' )
			->addArgument( $options )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'optimus_webp_subscriber', 'WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Optimus_Subscriber' )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'bigcommerce_subscriber', 'WP_Rocket\Subscriber\Third_Party\Plugins\Ecommerce\BigCommerce_Subscriber' )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'beaverbuilder_subscriber', 'WP_Rocket\ThirdParty\Plugins\PageBuilder\BeaverBuilder' )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'amp_subscriber', 'WP_Rocket\ThirdParty\Plugins\Optimization\AMP' )
			->addArgument( $options )->addArgument( $this->getContainer()->get( 'cdn_subscriber' ) )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'simple_custom_css', 'WP_Rocket\ThirdParty\Plugins\SimpleCustomCss' )
			->addArgument( WP_ROCKET_CACHE_BUSTING_PATH )->addArgument( WP_ROCKET_CACHE_BUSTING_URL )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'pdfembedder', 'WP_Rocket\ThirdParty\Plugins\PDFEmbedder' )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'mod_pagespeed', 'WP_Rocket\ThirdParty\Plugins\ModPagespeed' )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'adthrive', 'WP_Rocket\ThirdParty\Plugins\Ads\Adthrive' )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'autoptimize', 'WP_Rocket\ThirdParty\Plugins\Optimization\Autoptimize' )
			->addArgument( $options )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'wp-meteor', 'WP_Rocket\ThirdParty\Plugins\Optimization\WPMeteor' )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'revolution_slider_subscriber', 'WP_Rocket\ThirdParty\Plugins\RevolutionSlider' )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'wordfence_subscriber', 'WP_Rocket\ThirdParty\Plugins\Security\WordFenceCompatibility' )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'ezoic', 'WP_Rocket\ThirdParty\Plugins\Optimization\Ezoic' )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'thirstyaffiliates', 'WP_Rocket\ThirdParty\Plugins\ThirstyAffiliates' )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'pwa', 'WP_Rocket\ThirdParty\Plugins\PWA' )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'yoast_seo', 'WP_Rocket\ThirdParty\Plugins\SEO\Yoast' )
			->addArgument( $options )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'flatsome', 'WP_Rocket\ThirdParty\Themes\Flatsome' )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'convertplug', 'WP_Rocket\ThirdParty\Plugins\ConvertPlug' )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'inline_related_posts', 'WP_Rocket\ThirdParty\Plugins\InlineRelatedPosts' )
			->addTag( 'common_subscriber' );
	}
}
