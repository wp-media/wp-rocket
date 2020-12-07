<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals
defined( 'ABSPATH' ) || exit;

$aioseo_v3 = defined( 'AIOSEOP_VERSION' );
$aioseo_v4 = defined( 'AIOSEO_VERSION' ) && function_exists( 'aioseo' );
if ( $aioseo_v3 || $aioseo_v4 ) :
	$aioseop_options = '';
	$sitemap_enabled  = false;
	if ( $aioseo_v3 ) {
		$aioseop_options = get_option( 'aioseop_options' );
		$sitemap_enabled  = isset( $aioseop_options['modules']['aiosp_feature_manager_options']['aiosp_feature_manager_enable_sitemap'] ) && 'on' === $aioseop_options['modules']['aiosp_feature_manager_options']['aiosp_feature_manager_enable_sitemap'];
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
		 * Add All in One SEO Sitemap option to WP Rocket options
		 *
		 * @since 2.8
		 * @author Remy Perona
		 *
		 * @param Array $options Array of WP Rocket options.
		 * @return Array Updated array of WP Rocket options
		 */
		function rocket_add_all_in_one_seo_sitemap_option( $options ) {
			$options['all_in_one_seo_xml_sitemap'] = 0;

			return $options;
		}
		add_filter( 'rocket_first_install_options', 'rocket_add_all_in_one_seo_sitemap_option' );

		/**
		 * Sanitize the AIO SEO option value
		 *
		 * @since 2.8
		 * @author Remy Perona
		 *
		 * @param Array $inputs Array of inputs values.
		 * @return Array Updated array of inputs $values
		 */
		function rocket_all_in_one_seo_sitemap_option_sanitize( $inputs ) {
			$inputs['all_in_one_seo_xml_sitemap'] = ! empty( $inputs['all_in_one_seo_xml_sitemap'] ) ? 1 : 0;

			return $inputs;
		}
		add_filter( 'rocket_inputs_sanitize', 'rocket_all_in_one_seo_sitemap_option_sanitize' );

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

		/**
		 * Add All in One SEO Sitemap sub-option on WP Rocket settings page
		 *
		 * @since 2.8
		 * @author Remy Perona
		 *
		 * @param Array $options Array of WP Rocket options.
		 * @return Array Updated array of WP Rocket options
		 */
		function rocket_sitemap_preload_all_in_one_seo_option( $options ) {
			$options['all_in_one_seo_xml_sitemap'] = [
				'type'              => 'checkbox',
				'container_class'   => [
					'wpr-field--children',
				],
				'label'             => __( 'All in One SEO XML sitemap', 'rocket' ),
				// translators: %s = Name of the plugin.
				'description'       => sprintf( __( 'We automatically detected the sitemap generated by the %s plugin. You can check the option to preload it.', 'rocket' ), 'All in One SEO' ),
				'parent'            => 'sitemap_preload',
				'section'           => 'preload_section',
				'page'              => 'preload',
				'default'           => 0,
				'sanitize_callback' => 'sanitize_checkbox',
			];

			return $options;
		}
		add_filter( 'rocket_sitemap_preload_options', 'rocket_sitemap_preload_all_in_one_seo_option' );
	}
endif;

