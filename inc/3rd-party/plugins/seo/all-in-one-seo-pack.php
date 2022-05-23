<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals
defined( 'ABSPATH' ) || exit;

$aioseo_v3 = defined( 'AIOSEOP_VERSION' );
$aioseo_v4 = defined( 'AIOSEO_VERSION' ) && function_exists( 'aioseo' );

if ( $aioseo_v3 || $aioseo_v4 ) :
	$sitemap_enabled = false;
	if ( $aioseo_v3 ) {
		$aioseop_options = get_option( 'aioseop_options' );
		$sitemap_enabled = ( isset( $aioseop_options['modules']['aiosp_feature_manager_options']['aiosp_feature_manager_enable_sitemap'] ) && 'on' === $aioseop_options['modules']['aiosp_feature_manager_options']['aiosp_feature_manager_enable_sitemap'] ) || ( ! isset( $aioseop_options['modules']['aiosp_feature_manager_options'] ) && isset( $aioseop_options['modules']['aiosp_sitemap_options'] ) );
	}

	/**
	 * Improvement with All in One SEO Pack: auto-detect the XML sitemaps for the preload option
	 *
	 * @since 2.8
	 * @author Remy Perona
	 */
	if (
		( $aioseo_v3 && $sitemap_enabled ) ||
		( $aioseo_v4 && aioseo()->options->sitemap->general->enable )
	) {
		/**
		 * Add All in One SEO Sitemap to the preload list
		 *
		 * @since 2.8
		 * @author Remy Perona
		 *
		 * @param Array $sitemaps Array of sitemaps to preload.
		 * @return Array Updated array of sitemaps to preload
		 */
		function rocket_add_all_in_one_seo_sitemap( $sitemaps ) {
			if ( ! get_rocket_option( 'all_in_one_seo_xml_sitemap', false ) ) {
				return $sitemaps;
			}

			$aioseo_v3 = defined( 'AIOSEOP_VERSION' );
			$aioseo_v4 = defined( 'AIOSEO_VERSION' ) && function_exists( 'aioseo' );

			if ( ! $aioseo_v3 && ! $aioseo_v4 ) {
				return $sitemaps;
			}

			$sitemap_enabled = false;
			if ( $aioseo_v3 ) {
				$aioseop_options = get_option( 'aioseop_options' );
				$sitemap_enabled = ( isset( $aioseop_options['modules']['aiosp_feature_manager_options']['aiosp_feature_manager_enable_sitemap'] ) && 'on' === $aioseop_options['modules']['aiosp_feature_manager_options']['aiosp_feature_manager_enable_sitemap'] ) || ( ! isset( $aioseop_options['modules']['aiosp_feature_manager_options'] ) && isset( $aioseop_options['modules']['aiosp_sitemap_options'] ) );
			}

			if (
				( $aioseo_v3 && ! $sitemap_enabled ) ||
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
		add_filter( 'rocket_sitemap_preload_list', 'rocket_add_all_in_one_seo_sitemap' );
	}
endif;
