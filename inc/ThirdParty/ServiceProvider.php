<?php
namespace WP_Rocket\ThirdParty;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\EWWW_Subscriber;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Optimus_Subscriber;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\ShortPixel_Subscriber;
use WP_Rocket\Subscriber\Third_Party\Plugins\Mobile_Subscriber;
use WP_Rocket\Subscriber\Third_Party\Plugins\NGG_Subscriber;
use WP_Rocket\Subscriber\Third_Party\Plugins\SyntaxHighlighter_Subscriber;
use WP_Rocket\ThirdParty\Plugins\Ads\Adthrive;
use WP_Rocket\ThirdParty\Plugins\ConvertPlug;
use WP_Rocket\ThirdParty\Plugins\Ecommerce\BigCommerce;
use WP_Rocket\ThirdParty\Plugins\Ecommerce\WooCommerceSubscriber;
use WP_Rocket\ThirdParty\Plugins\I18n\TranslatePress;
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
use WP_Rocket\ThirdParty\Plugins\TheEventsCalendar;
use WP_Rocket\ThirdParty\Plugins\ThirstyAffiliates;
use WP_Rocket\ThirdParty\Plugins\UnlimitedElements;
use WP_Rocket\ThirdParty\Plugins\CDN\{Cloudflare, CloudflareFacade};
use WP_Rocket\ThirdParty\Plugins\Jetpack;
use WP_Rocket\ThirdParty\Plugins\WPGeotargeting;
use WP_Rocket\ThirdParty\Plugins\ContactForm7;
use WP_Rocket\ThirdParty\Plugins\SEO\RankMathSEO;
use WP_Rocket\ThirdParty\Plugins\SEO\AllInOneSEOPack;
use WP_Rocket\ThirdParty\Plugins\SEO\SEOPress;
use WP_Rocket\ThirdParty\Plugins\SEO\TheSEOFramework;
use WP_Rocket\ThirdParty\Plugins\Optimization\RocketLazyLoad;
use WP_Rocket\ThirdParty\Plugins\Optimization\Perfmatters;
use WP_Rocket\ThirdParty\Plugins\Optimization\RapidLoad;
use WP_Rocket\ThirdParty\Plugins\I18n\Weglot;

/**
 * Service provider for WP Rocket third party compatibility
 */
class ServiceProvider extends AbstractServiceProvider {
	/**
	 * Array of services provided by this service provider
	 *
	 * @var array
	 */
	protected $provides = [
		'mobile_subscriber',
		'woocommerce_subscriber',
		'syntaxhighlighter_subscriber',
		'elementor_subscriber',
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
		'mod_pagespeed',
		'adthrive',
		'autoptimize',
		'wp-meteor',
		'revolution_slider_subscriber',
		'wordfence_subscriber',
		'ezoic',
		'pwa',
		'convertplug',
		'unlimited_elements',
		'inline_related_posts',
		'jetpack',
		'rank_math_seo',
		'all_in_one_seo_pack',
		'seopress',
		'the_seo_framework',
		'wpml',
		'cloudflare_plugin_facade',
		'cloudflare_plugin_subscriber',
		'rocket_lazy_load',
		'the_events_calendar',
		'perfmatters',
		'rapidload',
		'translatepress',
		'wpgeotargeting',
		'weglot',
		'contactform7',
	];

	/**
	 * Check if the service provider provides a specific service.
	 *
	 * @param string $id The id of the service.
	 *
	 * @return bool
	 */
	public function provides( string $id ): bool {
		return in_array( $id, $this->provides, true );
	}

	/**
	 * Registers the subscribers in the container
	 *
	 * @since 3.3
	 *
	 * @return void
	 */
	public function register(): void {
		$options = $this->getContainer()->get( 'options' );

		$this->getContainer()
			->addShared( 'mobile_subscriber', Mobile_Subscriber::class );
		$this->getContainer()
			->addShared( 'elementor_subscriber', Elementor::class )
			->addArgument( $options )
			->addArgument( rocket_direct_filesystem() )
			->addArgument( $this->getContainer()->get( 'delay_js_html' ) );
		$this->getContainer()
			->addShared( 'woocommerce_subscriber', WooCommerceSubscriber::class )
			->addArgument( $this->getContainer()->get( 'delay_js_html' ) );
		$this->getContainer()
			->addShared( 'syntaxhighlighter_subscriber', SyntaxHighlighter_Subscriber::class );
		$this->getContainer()
			->addShared( 'ngg_subscriber', NGG_Subscriber::class );
		$this->getContainer()
			->addShared( 'smush_subscriber', Smush::class )
			->addArgument( $this->getContainer()->get( 'options_api' ) )
			->addArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()
			->addShared( 'imagify_webp_subscriber', Imagify_Subscriber::class )
			->addArgument( $options );
		$this->getContainer()
			->addShared( 'shortpixel_webp_subscriber', ShortPixel_Subscriber::class )
			->addArgument( $options );
		$this->getContainer()
			->addShared( 'ewww_webp_subscriber', EWWW_Subscriber::class )
			->addArgument( $options );
		$this->getContainer()
			->addShared( 'optimus_webp_subscriber', Optimus_Subscriber::class );
		$this->getContainer()
			->addShared( 'bigcommerce_subscriber', BigCommerce::class );
		$this->getContainer()
			->addShared( 'beaverbuilder_subscriber', BeaverBuilder::class );
		$this->getContainer()
			->addShared( 'amp_subscriber', AMP::class )
			->addArgument( $this->getContainer()->get( 'cdn_subscriber' ) );
		$this->getContainer()
			->addShared( 'simple_custom_css', SimpleCustomCss::class )
			->addArgument( WP_ROCKET_CACHE_BUSTING_PATH )->addArgument( WP_ROCKET_CACHE_BUSTING_URL );
		$this->getContainer()
			->addShared( 'pdfembedder', PDFEmbedder::class );
		$this->getContainer()
			->addShared( 'mod_pagespeed', ModPagespeed::class );
		$this->getContainer()
			->addShared( 'adthrive', Adthrive::class );
		$this->getContainer()
			->addShared( 'autoptimize', Autoptimize::class )
			->addArgument( $options );
		$this->getContainer()
			->addShared( 'wp-meteor', WPMeteor::class );
		$this->getContainer()
			->addShared( 'revolution_slider_subscriber', RevolutionSlider::class );
		$this->getContainer()
			->addShared( 'wordfence_subscriber', WordFenceCompatibility::class );
		$this->getContainer()
			->addShared( 'ezoic', Ezoic::class );
		$this->getContainer()
			->addShared( 'thirstyaffiliates', ThirstyAffiliates::class );
		$this->getContainer()
			->addShared( 'pwa', PWA::class );
		$this->getContainer()
			->addShared( 'yoast_seo', Yoast::class );
		$this->getContainer()
			->addShared( 'convertplug', ConvertPlug::class );
		$this->getContainer()
			->addShared( 'unlimited_elements', UnlimitedElements::class );
		$this->getContainer()
			->addShared( 'inline_related_posts', InlineRelatedPosts::class );
		$this->getContainer()
			->addShared( 'wpml', WPML::class );
		$this->getContainer()->add( 'cloudflare_plugin_facade', CloudflareFacade::class );
		$this->getContainer()
			->addShared( 'cloudflare_plugin_subscriber', Cloudflare::class )
			->addArgument( $options )
			->addArgument( $this->getContainer()->get( 'options_api' ) )
			->addArgument( $this->getContainer()->get( 'beacon' ) )
			->addArgument( $this->getContainer()->get( 'cloudflare_plugin_facade' ) );
		$this->getContainer()
			->addShared( 'jetpack', Jetpack::class )
			->addArgument( $options );
		$this->getContainer()
			->addShared( 'convertplug', ConvertPlug::class );
		$this->getContainer()
			->addShared( 'rank_math_seo', RankMathSEO::class );
		$this->getContainer()
			->addShared( 'all_in_one_seo_pack', AllInOneSEOPack::class )
			->addArgument( $options );
		$this->getContainer()
			->addShared( 'seopress', SEOPress::class )
			->addArgument( $options );
		$this->getContainer()
			->addShared( 'the_seo_framework', TheSEOFramework::class )
			->addArgument( $options );
		$this->getContainer()
			->addShared( 'rocket_lazy_load', RocketLazyLoad::class );
		$this->getContainer()
			->addShared( 'the_events_calendar', TheEventsCalendar::class );
		$this->getContainer()
			->addShared( 'perfmatters', Perfmatters::class );
		$this->getContainer()
			->addShared( 'rapidload', RapidLoad::class );
		$this->getContainer()
			->addShared( 'weglot', Weglot::class );
		$this->getContainer()->addShared( 'translatepress', TranslatePress::class );
		$this->getContainer()->addShared( 'wpgeotargeting', WPGeotargeting::class );
		$this->getContainer()->addShared( 'contactform7', ContactForm7::class );
	}
}
