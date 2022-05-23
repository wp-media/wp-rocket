<?php
/**
 * The wp-rocket compatibility functionality.
 *
 * @since      3.2.3
 * @package    Rank Math
 * @subpackage RankMath\Compatibility
 * @author     MyThemeShop <admin@mythemeshop.com>
 */

defined( 'ABSPATH' ) || exit;

// Ealry Bail!!
if ( ! defined( 'RANK_MATH_FILE' ) || ! \RankMath\Helper::is_module_active( 'sitemap' ) ) {
	return;
}

/**
 * Add SEO sitemap URL to the sitemaps to preload
 *
 * @since 3.2.3
 *
 * @param array $sitemaps Sitemaps to preload.
 * @return array Updated Sitemaps to preload
 */
function rank_math_rocket_sitemap( $sitemaps ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	if ( get_rocket_option( 'rank_math_xml_sitemap', false ) ) {
		$sitemaps[] = \RankMath\Sitemap\Router::get_base_url( 'sitemap_index.xml' );
	}

	return $sitemaps;
}
add_filter( 'rocket_sitemap_preload_list', 'rank_math_rocket_sitemap' );
