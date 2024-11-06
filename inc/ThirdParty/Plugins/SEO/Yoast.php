<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Plugins\SEO;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Yoast implements Subscriber_Interface {

	/**
	 * Array of events this subscriber listens to
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_sitemap_preload_list' => [ 'add_sitemap', 15 ],
		];
	}

	/**
	 * Add Yoast SEO sitemap URL to the sitemaps to preload
	 *
	 * @since 2.8
	 *
	 * @param array $sitemaps An array of sitemaps to preload.
	 *
	 * @return array
	 */
	public function add_sitemap( array $sitemaps ): array {
		if ( ! $this->is_sitemap_enabled() ) {
			return $sitemaps;
		}

		if ( ! class_exists( 'WPSEO_Sitemaps_Router' ) ) {
			return $sitemaps;
		}

		$sitemaps[] = \WPSEO_Sitemaps_Router::get_base_url( 'sitemap_index.xml' );

		return $sitemaps;
	}

	/**
	 * Checks if sitemap is enabled in Yoast SEO
	 *
	 * @since 3.11.1
	 *
	 * @return bool
	 */
	private function is_sitemap_enabled(): bool {
		static $enabled = null;

		if ( ! is_null( $enabled ) ) {
			return $enabled;
		}

		if ( ! rocket_has_constant( 'WPSEO_VERSION' ) ) {
			$enabled = false;

			return $enabled;
		}

		$yoast_seo_xml = get_option( 'wpseo_xml', [] ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

		if ( version_compare( rocket_get_constant( 'WPSEO_VERSION', '' ), '7.0' ) >= 0 ) {
			$yoast_seo                         = get_option( 'wpseo', [] ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
			$yoast_seo_xml['enablexmlsitemap'] = isset( $yoast_seo['enable_xml_sitemap'] ) && $yoast_seo['enable_xml_sitemap']; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
		}

		$enabled = (bool) $yoast_seo_xml['enablexmlsitemap'];

		return $enabled;
	}
}
