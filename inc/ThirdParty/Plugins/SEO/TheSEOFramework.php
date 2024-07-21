<?php

namespace WP_Rocket\ThirdParty\Plugins\SEO;

use The_SEO_Framework\Bridges\Sitemap;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

class TheSEOFramework implements Subscriber_Interface {

	/**
	 * Option instance.
	 *
	 * @var Options_Data
	 */
	protected $option;

	/**
	 * Instantiate the class.
	 *
	 * @param Options_Data $option Option instance.
	 */
	public function __construct( Options_Data $option ) {
		$this->option = $option;
	}

	/**
	 * Subscribed events.
	 */
	public static function get_subscribed_events() {
		if ( ! function_exists( 'the_seo_framework' ) ) {
			return [];
		}
		$tsf = the_seo_framework();

		// Either TSF < 3.1, or the plugin's silenced (soft-disabled) via a drop-in.
		if ( empty( $tsf->loaded ) ) {
			return [];
		}

		/**
		 * 1. Performs option & other checks.
		 * 2. Checks for conflicting sitemap plugins that might prevent loading.
		 *
		 * These methods cache their output at runtime.
		 *
		 * @link https://github.com/wp-media/wp-rocket/issues/899
		 */
		// @phpstan-ignore-next-line
		if ( ! $tsf->can_run_sitemap() ) {
			return [];
		}

		return [
			'rocket_sitemap_preload_list' => [ 'add_tsf_sitemap_to_preload', 15 ],
		];
	}

	/**
	 * Adds TSF sitemap URLs to preload.
	 *
	 * @param array $sitemaps Sitemaps to preload.
	 * @return array Updated Sitemaps to preload
	 */
	public function add_tsf_sitemap_to_preload( $sitemaps ) {

		// The autoloader in TSF doesn't check for file_exists(). So, use version compare instead to prevent fatal errors.
		if ( version_compare( rocket_get_constant( 'THE_SEO_FRAMEWORK_VERSION', false ), '4.0', '>=' ) ) {
			// TSF 4.0+. Expect the class to exist indefinitely.
			$sitemap_bridge = Sitemap::get_instance();

			foreach ( $sitemap_bridge->get_sitemap_endpoint_list() as $id => $data ) {
				// When the sitemap is good enough for a robots display, we determine it as valid for precaching.
				// Non-robots display types are among the stylesheet endpoint, or the Yoast SEO-compatible endpoint.
				// In other words, this enables support for ALL current and future public sitemap endpoints.
				if ( ! empty( $data['robots'] ) ) {
					$sitemaps[] = $sitemap_bridge->get_expected_sitemap_endpoint_url( $id );
				}
			}
		} else {
			// Deprecated. TSF <4.0.
			$sitemaps[] = the_seo_framework()->get_sitemap_xml_url();
		}

		return $sitemaps;
	}
}
