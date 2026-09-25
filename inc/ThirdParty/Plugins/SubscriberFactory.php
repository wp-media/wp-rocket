<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Plugins;

use WP_Rocket\Dependencies\League\Container\Argument\Literal\StringArgument;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Optimus_Subscriber;
use WP_Rocket\Subscriber\Third_Party\Plugins\Mobile_Subscriber;
use WP_Rocket\Subscriber\Third_Party\Plugins\NGG_Subscriber;
use WP_Rocket\Subscriber\Third_Party\Plugins\SyntaxHighlighter_Subscriber;
use WP_Rocket\ThirdParty\Plugins\Ads\Adthrive;
use WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare;
use WP_Rocket\ThirdParty\Plugins\ContactForm7;
use WP_Rocket\ThirdParty\Plugins\ConvertPlug;
use WP_Rocket\ThirdParty\Plugins\Cookie\Termly;
use WP_Rocket\ThirdParty\Plugins\Ecommerce\BigCommerce;
use WP_Rocket\ThirdParty\Plugins\Ecommerce\WooCommerceSubscriber;
use WP_Rocket\ThirdParty\Plugins\EWWW;
use WP_Rocket\ThirdParty\Plugins\I18n\TranslatePress;
use WP_Rocket\ThirdParty\Plugins\I18n\Weglot;
use WP_Rocket\ThirdParty\Plugins\I18n\WPML;
use WP_Rocket\ThirdParty\Plugins\InlineRelatedPosts;
use WP_Rocket\ThirdParty\Plugins\Jetpack;
use WP_Rocket\ThirdParty\Plugins\Optimization\AMP;
use WP_Rocket\ThirdParty\Plugins\Optimization\Autoptimize;
use WP_Rocket\ThirdParty\Plugins\Optimization\Perfmatters;
use WP_Rocket\ThirdParty\Plugins\Optimization\RapidLoad;
use WP_Rocket\ThirdParty\Plugins\Optimization\RocketLazyLoad;
use WP_Rocket\ThirdParty\Plugins\Optimization\WPMeteor;
use WP_Rocket\ThirdParty\Plugins\Optimole;
use WP_Rocket\ThirdParty\Plugins\PageBuilder\BeaverBuilder;
use WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor;
use WP_Rocket\ThirdParty\Plugins\PDFEmbedder;
use WP_Rocket\ThirdParty\Plugins\PWA;
use WP_Rocket\ThirdParty\Plugins\RevolutionSlider;
use WP_Rocket\ThirdParty\Plugins\Security\WordFenceCompatibility;
use WP_Rocket\ThirdParty\Plugins\SEO\AllInOneSEOPack;
use WP_Rocket\ThirdParty\Plugins\SEO\RankMathSEO;
use WP_Rocket\ThirdParty\Plugins\SEO\SEOPress;
use WP_Rocket\ThirdParty\Plugins\SEO\TheSEOFramework;
use WP_Rocket\ThirdParty\Plugins\SEO\Yoast;
use WP_Rocket\ThirdParty\Plugins\ShortPixel;
use WP_Rocket\ThirdParty\Plugins\SimpleCustomCss;
use WP_Rocket\ThirdParty\Plugins\Smush;
use WP_Rocket\ThirdParty\Plugins\TheEventsCalendar;
use WP_Rocket\ThirdParty\Plugins\ThirstyAffiliates;
use WP_Rocket\ThirdParty\Plugins\UnlimitedElements;
use WP_Rocket\ThirdParty\Plugins\WPGeotargeting;

/**
 * Authoritative id => class registry and constructor argument builder for the
 * resolver-gate-able plugin compatibility subscribers.
 *
 * Single source of truth for the factory-owned plugin ids (Phase 0 scaffolding
 * for issue #6418). `get_registry()` is a pure literal map, safe to enumerate
 * without side effects; `get_arguments()` builds the runtime args for a single
 * id only, at register time.
 */
class SubscriberFactory {
	/**
	 * Authoritative id => FQCN map for resolver-gate-able plugin compat subscribers.
	 *
	 * @return array<string,string>
	 */
	public function get_registry(): array {
		return [
			'mobile_subscriber'            => Mobile_Subscriber::class,
			// syntaxhighlighter_subscriber is ordered before elementor_subscriber: both hook
			// `rocket_exclude_js` at the default priority (hook-collision scan, issue #6418
			// Phase 0); this preserves their pre-refactor relative registration order.
			'syntaxhighlighter_subscriber' => SyntaxHighlighter_Subscriber::class,
			'elementor_subscriber'         => Elementor::class,
			'woocommerce_subscriber'       => WooCommerceSubscriber::class,
			'ngg_subscriber'               => NGG_Subscriber::class,
			'smush_subscriber'             => Smush::class,
			'imagify_webp_subscriber'      => Imagify_Subscriber::class,
			'shortpixel_webp_subscriber'   => ShortPixel::class,
			'ewww_webp_subscriber'         => EWWW::class,
			'optimus_webp_subscriber'      => Optimus_Subscriber::class,
			'bigcommerce_subscriber'       => BigCommerce::class,
			'beaverbuilder_subscriber'     => BeaverBuilder::class,
			'amp_subscriber'               => AMP::class,
			'simple_custom_css'            => SimpleCustomCss::class,
			'pdfembedder'                  => PDFEmbedder::class,
			'adthrive'                     => Adthrive::class,
			'autoptimize'                  => Autoptimize::class,
			'wp-meteor'                    => WPMeteor::class,
			'revolution_slider_subscriber' => RevolutionSlider::class,
			'wordfence_subscriber'         => WordFenceCompatibility::class,
			'thirstyaffiliates'            => ThirstyAffiliates::class,
			'pwa'                          => PWA::class,
			'yoast_seo'                    => Yoast::class,
			'convertplug'                  => ConvertPlug::class,
			'unlimited_elements'           => UnlimitedElements::class,
			'inline_related_posts'         => InlineRelatedPosts::class,
			'wpml'                         => WPML::class,
			'cloudflare_plugin_subscriber' => Cloudflare::class,
			'jetpack'                      => Jetpack::class,
			'rank_math_seo'                => RankMathSEO::class,
			'all_in_one_seo_pack'          => AllInOneSEOPack::class,
			'seopress'                     => SEOPress::class,
			'the_seo_framework'            => TheSEOFramework::class,
			'rocket_lazy_load'             => RocketLazyLoad::class,
			'the_events_calendar'          => TheEventsCalendar::class,
			'perfmatters'                  => Perfmatters::class,
			'rapidload'                    => RapidLoad::class,
			'weglot'                       => Weglot::class,
			'translatepress'               => TranslatePress::class,
			'wpgeotargeting'               => WPGeotargeting::class,
			'contactform7'                 => ContactForm7::class,
			'termly_subscriber'            => Termly::class,
			'optimole_subscriber'          => Optimole::class,
		];
	}

	/**
	 * Constructor arguments for a given id, verbatim from the current register().
	 *
	 * @param string $id Plugin subscriber id.
	 *
	 * @return array
	 */
	public function get_arguments( string $id ): array {
		// Container-id arguments only: plain lazy references, no runtime construction.
		$lazy_arguments = [
			'woocommerce_subscriber'       => [ 'delay_js_html' ],
			'smush_subscriber'             => [ 'options_api', 'options' ],
			'imagify_webp_subscriber'      => [ 'options' ],
			'shortpixel_webp_subscriber'   => [ 'options' ],
			'ewww_webp_subscriber'         => [ 'options' ],
			'autoptimize'                  => [ 'options' ],
			'jetpack'                      => [ 'options' ],
			'all_in_one_seo_pack'          => [ 'options' ],
			'seopress'                     => [ 'options' ],
			'the_seo_framework'            => [ 'options' ],
			'amp_subscriber'               => [ 'cdn_subscriber' ],
			'cloudflare_plugin_subscriber' => [ 'options', 'options_api', 'beacon', 'cloudflare_plugin_facade' ],
		];

		if ( isset( $lazy_arguments[ $id ] ) ) {
			return $lazy_arguments[ $id ];
		}

		// Ids whose arguments must be constructed at register time, built only when requested.
		if ( 'elementor_subscriber' === $id ) {
			return [ 'options', rocket_direct_filesystem(), 'delay_js_html' ];
		}

		if ( 'simple_custom_css' === $id ) {
			return [
				new StringArgument( rocket_get_constant( 'WP_ROCKET_CACHE_BUSTING_PATH', '' ) ),
				new StringArgument( rocket_get_constant( 'WP_ROCKET_CACHE_BUSTING_URL', '' ) ),
			];
		}

		return [];
	}
}
