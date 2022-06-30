<?php
namespace WP_Rocket\ThirdParty;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Subscriber\Third_Party\Plugins\Ecommerce\BigCommerce_Subscriber;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\EWWW_Subscriber;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Optimus_Subscriber;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\ShortPixel_Subscriber;
use WP_Rocket\Subscriber\Third_Party\Plugins\Mobile_Subscriber;
use WP_Rocket\Subscriber\Third_Party\Plugins\NGG_Subscriber;
use WP_Rocket\Subscriber\Third_Party\Plugins\SyntaxHighlighter_Subscriber;
use WP_Rocket\ThirdParty\Plugins\Ads\Adthrive;
use WP_Rocket\ThirdParty\Plugins\ConvertPlug;
use WP_Rocket\ThirdParty\Plugins\Ecommerce\WooCommerceSubscriber;
use WP_Rocket\ThirdParty\Plugins\I18n\WPML;
use WP_Rocket\ThirdParty\Plugins\InlineRelatedPosts;
use WP_Rocket\ThirdParty\Plugins\ModPagespeed;
use WP_Rocket\ThirdParty\Plugins\Optimization\AMP;
use WP_Rocket\ThirdParty\Plugins\Optimization\Autoptimize;
use WP_Rocket\ThirdParty\Plugins\Optimization\Ezoic;
use WP_Rocket\ThirdParty\Plugins\Optimization\WPMeteor;
use WP_Rocket\ThirdParty\Plugins\PageBuilder\BeaverBuilder;
use WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor;
use WP_Rocket\ThirdParty\Plugins\PDFEmbedder;
use WP_Rocket\ThirdParty\Plugins\PWA;
use WP_Rocket\ThirdParty\Plugins\RevolutionSlider;
use WP_Rocket\ThirdParty\Plugins\Security\WordFenceCompatibility;
use WP_Rocket\ThirdParty\Plugins\SEO\Yoast;
use WP_Rocket\ThirdParty\Plugins\SimpleCustomCss;
use WP_Rocket\ThirdParty\Plugins\Smush;
use WP_Rocket\ThirdParty\Plugins\ThirstyAffiliates;
use WP_Rocket\ThirdParty\Plugins\UnlimitedElements;
use WP_Rocket\ThirdParty\Themes\Avada;
use WP_Rocket\ThirdParty\Themes\Bridge;
use WP_Rocket\ThirdParty\Themes\Divi;
use WP_Rocket\ThirdParty\Themes\Flatsome;

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
			->share( 'mobile_subscriber', Mobile_Subscriber::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'elementor_subscriber', Elementor::class )
			->addArgument( $options )
			->addArgument( rocket_direct_filesystem() )
			->addArgument( $this->getContainer()->get( 'delay_js_html' ) )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'woocommerce_subscriber', WooCommerceSubscriber::class )
			->addArgument( $this->getContainer()->get( 'delay_js_html' ) )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'syntaxhighlighter_subscriber', SyntaxHighlighter_Subscriber::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'bridge_subscriber', Bridge::class )
			->addArgument( $options )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'divi', Divi::class )
			->addArgument( $this->getContainer()->get( 'options_api' ) )
			->addArgument( $options )
			->addArgument( $this->getContainer()->get( 'delay_js_html' ) )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'avada_subscriber', Avada::class )
			->addArgument( $options )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'ngg_subscriber', NGG_Subscriber::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'smush_subscriber', Smush::class )
			->addArgument( $this->getContainer()->get( 'options_api' ) )
			->addArgument( $this->getContainer()->get( 'options' ) )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'imagify_webp_subscriber', Imagify_Subscriber::class )
			->addArgument( $options )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'shortpixel_webp_subscriber', ShortPixel_Subscriber::class )
			->addArgument( $options )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'ewww_webp_subscriber', EWWW_Subscriber::class )
			->addArgument( $options )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'optimus_webp_subscriber', Optimus_Subscriber::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'bigcommerce_subscriber', BigCommerce_Subscriber::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'beaverbuilder_subscriber', BeaverBuilder::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'amp_subscriber', AMP::class )
			->addArgument( $options )->addArgument( $this->getContainer()->get( 'cdn_subscriber' ) )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'simple_custom_css', SimpleCustomCss::class )
			->addArgument( WP_ROCKET_CACHE_BUSTING_PATH )->addArgument( WP_ROCKET_CACHE_BUSTING_URL )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'pdfembedder', PDFEmbedder::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'mod_pagespeed', ModPagespeed::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'adthrive', Adthrive::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'autoptimize', Autoptimize::class )
			->addArgument( $options )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'wp-meteor', WPMeteor::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'revolution_slider_subscriber', RevolutionSlider::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'wordfence_subscriber', WordFenceCompatibility::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'ezoic', Ezoic::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'thirstyaffiliates', ThirstyAffiliates::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'pwa', PWA::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'yoast_seo', Yoast::class )
			->addArgument( $options )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'flatsome', Flatsome::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'convertplug', ConvertPlug::class )
			->addTag( 'common_subscriber' );

		$this->getContainer()
			->share( 'unlimited_elements', UnlimitedElements::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'inline_related_posts', InlineRelatedPosts::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'wpml', WPML::class )
			->addTag( 'common_subscriber' );
	}
}
