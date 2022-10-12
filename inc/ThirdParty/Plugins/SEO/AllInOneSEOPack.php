<?php

namespace WP_Rocket\ThirdParty\Plugins\SEO;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

class AllInOneSEOPack implements Subscriber_Interface {

	/**
	 * Option instance.
	 *
	 * @var Options_Data
	 */
	protected $option;

	/**
	 * Instantiate class.
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
		$aioseo_v3 = defined( 'AIOSEOP_VERSION' );
		$aioseo_v4 = defined( 'AIOSEO_VERSION' ) && function_exists( 'aioseo' );

		if ( ! $aioseo_v3 && ! $aioseo_v4 ) {
			return [];
		}

		return [
			'rocket_sitemap_preload_list' => [ 'add_all_in_one_seo_sitemap', 15 ],
		];
	}

	/**
	 * Add All in One SEO Sitemap to the preload list
	 *
	 * @param Array $sitemaps Array of sitemaps to preload.
	 * @return Array Updated array of sitemaps to preload
	 */
	public function add_all_in_one_seo_sitemap( $sitemaps ) {

		$aioseo_v3 = defined( 'AIOSEOP_VERSION' );
		$aioseo_v4 = defined( 'AIOSEO_VERSION' ) && function_exists( 'aioseo' );

		$sitemap_enabled = false;
		if ( $aioseo_v3 && ! $aioseo_v4 ) {
			$aioseop_options = get_option( 'aioseop_options' );
			$sitemap_enabled = ( isset( $aioseop_options['modules']['aiosp_feature_manager_options']['aiosp_feature_manager_enable_sitemap'] ) && 'on' === $aioseop_options['modules']['aiosp_feature_manager_options']['aiosp_feature_manager_enable_sitemap'] ) || ( ! isset( $aioseop_options['modules']['aiosp_feature_manager_options'] ) && isset( $aioseop_options['modules']['aiosp_sitemap_options'] ) );
		}

		if (
			( ! $aioseo_v4 && ! $sitemap_enabled ) ||
			( $aioseo_v4 && ! aioseo()->options->sitemap->general->enable )
		) {
			return $sitemaps;
		}

		if ( $aioseo_v3 ) {
			$sitemaps[] = trailingslashit( home_url() ) . apply_filters( 'aiosp_sitemap_filename', 'sitemap' ) . '.xml'; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
		} elseif ( $aioseo_v4 ) {
			$sitemaps[] = trailingslashit( home_url() ) . apply_filters( 'aioseo_sitemap_filename', 'sitemap' ) . '.xml'; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
		}

		return $sitemaps;
	}
}
