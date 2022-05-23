<?php

defined( 'ABSPATH' ) || exit;

if ( function_exists( 'seopress_get_toggle_xml_sitemap_option' ) && 1 === (int) seopress_get_toggle_xml_sitemap_option() ) :

	/**
	 * Improvement with SEOPress: auto-detect the XML sitemaps for the preload option
	 *
	 * @since 3.3.6
	 * @author Benjamin Denis
	 * @source ./yoast-seo.php (Remy Perona)
	 */
	if ( function_exists( 'seopress_xml_sitemap_general_enable_option' ) && 1 === (int) seopress_xml_sitemap_general_enable_option() ) {
		/**
		 * Add SEOPress sitemap URL to the sitemaps to preload
		 *
		 * @since 3.3.6
		 * @author Benjamin Denis
		 * @source ./yoast-seo.php (Remy Perona)
		 *
		 * @param array $sitemaps Sitemaps to preload.
		 * @return array Updated Sitemaps to preload
		 */
		function rocket_add_seopress_sitemap( $sitemaps ) {
			if ( get_rocket_option( 'seopress_xml_sitemap', false ) ) {
				$sitemaps[] = get_home_url() . '/sitemaps.xml';
			}

			return $sitemaps;
		}
		add_filter( 'rocket_sitemap_preload_list', 'rocket_add_seopress_sitemap' );
	}
endif;
