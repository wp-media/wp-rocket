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
		'unlimited_elements',
		'inline_related_posts',
		'wpml',
		'xstore',
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
			->share( 'mobile_subscriber', \WP_Rocket\Subscriber\Third_Party\Plugins\Mobile_Subscriber::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'elementor_subscriber', Plugins\PageBuilder\Elementor::class )
			->addArgument( $options )
			->addArgument( rocket_direct_filesystem() )
			->addArgument( $this->getContainer()->get( 'delay_js_html' ) )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'woocommerce_subscriber', Plugins\Ecommerce\WooCommerceSubscriber::class )
			->addArgument( $this->getContainer()->get( 'delay_js_html' ) )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'syntaxhighlighter_subscriber', \WP_Rocket\Subscriber\Third_Party\Plugins\SyntaxHighlighter_Subscriber::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'bridge_subscriber', Themes\Bridge::class )
			->addArgument( $options )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'divi', Themes\Divi::class )
			->addArgument( $this->getContainer()->get( 'options_api' ) )
			->addArgument( $options )
			->addArgument( $this->getContainer()->get( 'delay_js_html' ) )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'avada_subscriber', Themes\Avada::class )
			->addArgument( $options )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'ngg_subscriber', \WP_Rocket\Subscriber\Third_Party\Plugins\NGG_Subscriber::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'smush_subscriber', Plugins\Smush::class )
			->addArgument( $this->getContainer()->get( 'options_api' ) )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'imagify_webp_subscriber', \WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber::class )
			->addArgument( $options )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'shortpixel_webp_subscriber', \WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\ShortPixel_Subscriber::class )
			->addArgument( $options )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'ewww_webp_subscriber', \WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\EWWW_Subscriber::class )
			->addArgument( $options )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'optimus_webp_subscriber', \WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Optimus_Subscriber::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'bigcommerce_subscriber', \WP_Rocket\Subscriber\Third_Party\Plugins\Ecommerce\BigCommerce_Subscriber::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'beaverbuilder_subscriber', Plugins\PageBuilder\BeaverBuilder::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'amp_subscriber', Plugins\Optimization\AMP::class )
			->addArgument( $options )->addArgument( $this->getContainer()->get( 'cdn_subscriber' ) )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'simple_custom_css', Plugins\SimpleCustomCss::class )
			->addArgument( WP_ROCKET_CACHE_BUSTING_PATH )->addArgument( WP_ROCKET_CACHE_BUSTING_URL )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'pdfembedder', Plugins\PDFEmbedder::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'mod_pagespeed', Plugins\ModPagespeed::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'adthrive', Plugins\Ads\Adthrive::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'autoptimize', Plugins\Optimization\Autoptimize::class )
			->addArgument( $options )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'wp-meteor', Plugins\Optimization\WPMeteor::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'revolution_slider_subscriber', Plugins\RevolutionSlider::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'wordfence_subscriber', Plugins\Security\WordFenceCompatibility::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'ezoic', Plugins\Optimization\Ezoic::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'thirstyaffiliates', Plugins\ThirstyAffiliates::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'pwa', Plugins\PWA::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'yoast_seo', Plugins\SEO\Yoast::class )
			->addArgument( $options )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'flatsome', Themes\Flatsome::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'convertplug', Plugins\ConvertPlug::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'unlimited_elements', Plugins\UnlimitedElements::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'inline_related_posts', Plugins\InlineRelatedPosts::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'wpml', Plugins\I18n\WPML::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'xstore', Themes\Xstore::class )
			->addTag( 'common_subscriber' );
	}
}
