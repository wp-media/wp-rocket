<?php
namespace WP_Rocket\ThirdParty;

use WP_Rocket\AbstractServiceProvider;
use WP_Rocket\Engine\CDN\ServiceProvider as CDNServiceProvider;
use WP_Rocket\Engine\Optimization\DelayJS\ServiceProvider as DelayJSServiceProvider;
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
use WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare;
use WP_Rocket\ThirdParty\Plugins\Jetpack;
use WP_Rocket\ThirdParty\Themes\MinimalistBlogger;
use WP_Rocket\ThirdParty\Plugins\SEO\RankMathSEO;
use WP_Rocket\ThirdParty\Plugins\SEO\AllInOneSEOPack;
use WP_Rocket\ThirdParty\Plugins\SEO\SEOPress;
use WP_Rocket\ThirdParty\Plugins\SEO\TheSEOFramework;
use WP_Rocket\ThirdParty\Plugins\Optimization\RocketLazyLoad;

/**
 * Service provider for WP Rocket third party compatibility
 *
 * @since 3.3
 */
class ServiceProvider extends AbstractServiceProvider {

	protected $simple_registration_classes = [
		Mobile_Subscriber::class => false,
		SyntaxHighlighter_Subscriber::class => false,
		NGG_Subscriber::class => false,
		Imagify_Subscriber::class => true,
		ShortPixel_Subscriber::class => true,
		EWWW_Subscriber::class => true,
		BigCommerce::class => false,
		BeaverBuilder::class => false,
		PDFEmbedder::class => false,
		ModPagespeed::class => false,
		Adthrive::class => false,
		Autoptimize::class => true,
		WPMeteor::class => false,
		RevolutionSlider::class => false,
		WordFenceCompatibility::class => false,
		Ezoic::class => false,
		ThirstyAffiliates::class => false,
		PWA::class => false,
		Yoast::class => true,
		UnlimitedElements::class => false,
		InlineRelatedPosts::class => false,
		Optimus_Subscriber::class => false,
		ConvertPlug::class => false,
		RocketLazyLoad::class => false,
		WPML::class => false,
		Cloudflare::class => true,
		Jetpack::class => true,
		RankMathSEO::class => true,
		AllInOneSEOPack::class => true,
		SEOPress::class => true,
		TheSEOFramework::class => true,
		TheEventsCalendar::class => false
	];

	public function get_common_subscribers(): array
	{

		$simple_registration_classes_ids = array_map(function ($class) {
			return $this->generate_id($class);
		}, array_keys($this->simple_registration_classes));

		$subscribers = array_merge($simple_registration_classes_ids, [
			'elementor_subscriber',
			'woocommerce_subscriber',
			'smush_subscriber',
			'amp_subscriber',
			'simple_custom_css',
		]);

		return array_map(function ($id) {
			return $this->generate_container_id( $id );
		}, $subscribers);
	}

	public function declare()
	{


		foreach ($this->simple_registration_classes as $simple_registration_class => $has_options) {
			$id = $this->generate_id($simple_registration_class);
			$this->register_service($id, function (string $id) use ($simple_registration_class, $has_options) {
				if(! $has_options ) {
					$this->share($id, $simple_registration_class )
						->addTag( 'common_subscriber' );
				}
				$this
					->share( $id, $simple_registration_class )
					->addArgument( $this->get_external('options') )
					->addTag( 'common_subscriber' );
			});
		}

		$this->register_service('elementor_subscriber', function ($id) {
			$this
				->share( $id, Elementor::class )
				->addArgument( $this->get_external('options') )
				->addArgument( rocket_direct_filesystem() )
				->addArgument( $this->get_external( 'delay_js_html', DelayJSServiceProvider::class ) )
				->addTag( 'common_subscriber' );
		});

		$this->register_service('woocommerce_subscriber', function ($id) {
			$this
				->share( $id, WooCommerceSubscriber::class )
				->addArgument( $this->get_external( 'delay_js_html', DelayJSServiceProvider::class ) )
				->addTag( 'common_subscriber' );
		});

		$this->register_service('smush_subscriber', function ($id) {
			$this
				->share( $id, Smush::class )
				->addArgument( $this->get_external( 'options_api' ) )
				->addArgument( $this->get_external( 'options' ) )
				->addTag( 'common_subscriber' );
		});

		$this->register_service('amp_subscriber', function ($id) {
			$this
				->share( $id, AMP::class )
				->addArgument( $this->get_external( 'options' ) )
				->addArgument( $this->get_external( 'cdn_subscriber', CDNServiceProvider::class ) )
				->addTag( 'common_subscriber' );
		});

		$this->register_service('simple_custom_css', function ($id) {
			$this
				->share( $id, SimpleCustomCss::class )
				->addArgument( WP_ROCKET_CACHE_BUSTING_PATH )
				->addArgument( WP_ROCKET_CACHE_BUSTING_URL )
				->addTag( 'common_subscriber' );
		});
	}
}
