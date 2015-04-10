<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

/**
 * Check whether the plugin is active by checking the active_plugins list.
 *
 * @since 1.3.0
 *
 * @source wp-admin/includes/plugin.php
 */
function rocket_is_plugin_active( $plugin )
{
	return in_array( $plugin, (array) get_option( 'active_plugins', array() ) ) || rocket_is_plugin_active_for_network( $plugin );
}

/**
 * Check whether the plugin is active for the entire network.
 *
 * @since 1.3.0
 *
 * @source wp-admin/includes/plugin.php
 */
function rocket_is_plugin_active_for_network( $plugin )
{
	if ( !is_multisite() ) {
		return false;
	}

	$plugins = get_site_option( 'active_sitewide_plugins');
	if ( isset($plugins[$plugin]) ) {
		return true;
	}

	return false;
}

/**
 * Call the cache server to purge the cache with SuperCacher (SiteGround) Pretty good hosting!
 *
 * @since 2.3
 *
 * @return void
 */
function rocket_clean_supercacher() {
	if ( isset( $GLOBALS['sg_cachepress_supercacher'] ) && is_a( $GLOBALS['sg_cachepress_supercacher'], 'SG_CachePress_Supercacher' ) ) {
		$GLOBALS['sg_cachepress_supercacher']->purge_cache();
	}
}

/**
 * Call the cache server to purge the cache with StudioPress Accelerator.
 *
 * @since 2.5.5
 *
 * @return void
 */
function rocket_clean_studiopress_accelerator() {
	if ( isset( $GLOBALS['sp_accel_nginx_proxy_cache_purge'] ) && is_a( $GLOBALS['sp_accel_nginx_proxy_cache_purge'], 'SP_Accel_Nginx_Proxy_Cache_Purge' ) ) {
		$GLOBALS['sp_accel_nginx_proxy_cache_purge']->cache_flush_theme();
	}
}

/**
 * Call the cache server to purge the cache with Varnish HTTP Purge.
 *
 * @since 2.5.5
 *
 * @return void
 */
function rocket_clean_varnish_http_purge() {
	if ( class_exists( 'VarnishPurger' ) ) {
		$purger = new VarnishPurger();
		$purger->executePurge();
	}
}

/**
 * Check if a translation plugin is activated
 *
 * @since 2.0
 *
 * @return bool True if a plugin is activated
 */
function rocket_has_i18n()
{
	if ( rocket_is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' )  // WPML
		|| rocket_is_plugin_active( 'qtranslate/qtranslate.php' )  				// qTranslate
		|| rocket_is_plugin_active( 'polylang/polylang.php' ) ) { 				// Polylang
		return true;
	}

	return false;
}

/**
 * Get infos of all active languages
 *
 * @since 2.0
 *
 * @return array List of language code
 */
function get_rocket_i18n_code()
{
	if( ! rocket_has_i18n() ) {
		return false;
	}

	if ( rocket_is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
		return array_keys( $GLOBALS['sitepress']->get_active_languages() );
	}

	if ( rocket_is_plugin_active( 'qtranslate/qtranslate.php' ) ) {
		return $GLOBALS['q_config']['enabled_languages'];
	}

	if ( rocket_is_plugin_active( 'polylang/polylang.php' ) ) {
		return wp_list_pluck( $GLOBALS['polylang']->model->get_languages_list(), 'slug' );
	}
}

/**
 * Get all active languages URI
 *
 * @since 2.0
 *
 * @return array $urls List of all active languages URI
 */
function get_rocket_i18n_uri()
{
	$urls = array();
	if ( ! rocket_has_i18n() ) {
		$urls[] = home_url();
		return $urls;
	}

	if ( rocket_is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
		$langs = get_rocket_i18n_code();
		foreach ( $langs as $lang ) {
			$urls[] = $GLOBALS['sitepress']->language_url( $lang );
		}
	} elseif ( rocket_is_plugin_active( 'qtranslate/qtranslate.php' ) ) {
		$langs = get_rocket_i18n_code();
		foreach ( $langs as $lang ) {
			$urls[] = qtrans_convertURL( home_url(), $lang, true );
		}
	} elseif ( rocket_is_plugin_active( 'polylang/polylang.php' ) ) {
		$urls = wp_list_pluck( $GLOBALS['polylang']->model->get_languages_list(), 'home_url' );
	}

	return $urls;
}

/**
 * Get directories paths to preserve languages ​​when purging a domain
 * This function is required when the domains of languages (​​other than the default) are managed by subdirectories
 * By default, when you clear the cache of the french website with the domain example.com, all subdirectory like /en/ and /de/ are deleted.
 * But, if you have a domain for your english and german websites with example.com/en/ and example.com/de/, you want to keep the /en/ and /de/ directory when the french domain is cleared.
 *
 * @since 2.0
 *
 * @param string $current_lang The current language code
 * @return array $langs_to_preserve List of directories path to preserve
 */
function get_rocket_i18n_to_preserve( $current_lang )
{
	$langs_to_preserve = array();
	if ( ! rocket_has_i18n() ) {
		return $langs_to_preserve;
	}

	$langs = get_rocket_i18n_code();

	// Unset current lang to the preserve dirs
	$langs = array_flip( $langs );
	if( isset( $langs[$current_lang] ) ) {
		unset( $langs[$current_lang] );	
	}
	$langs = array_flip( $langs );

	// Stock all URLs of langs to preserve
	foreach ( $langs as $lang ) {
		list( $host, $path ) = get_rocket_parse_url( get_rocket_i18n_home_url( $lang ) );
		$langs_to_preserve[] = WP_ROCKET_CACHE_PATH . $host . '(.*)/' . trim( $path, '/' );
	}

	/**
	 * Filter directories path to preserve of cache purge
	 *
	 * @since 2.1
	 *
	 * @param array $langs_to_preserve List of directories path to preserve
	*/
	$langs_to_preserve = apply_filters( 'rocket_langs_to_preserve', $langs_to_preserve );

	return $langs_to_preserve;
}

/**
 * Get all languages subdomains URLs
 *
 * @since 2.1
 *
 * @return array $urls List of languages subdomains URLs
 */
function get_rocket_i18n_subdomains()
{
	if ( ! rocket_has_i18n() ) {
		return false;
	}

	$urls = array();
	if ( rocket_is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
		$option = get_option( 'icl_sitepress_settings' );
		if ( (int) $option['language_negotiation_type'] == 2 ) {
			$urls = get_rocket_i18n_uri();
		}
	} elseif ( rocket_is_plugin_active( 'qtranslate/qtranslate.php' ) ) {
		if( (int) $GLOBALS['q_config']['url_mode'] == 3 ) {
			$urls = get_rocket_i18n_uri();
		}
	} elseif ( rocket_is_plugin_active( 'polylang/polylang.php' ) ) {
		if ( (int) $GLOBALS['polylang']->options['force_lang'] == 2 ) {
			$urls = get_rocket_i18n_uri();
		}
	}

	return $urls;
}

/**
 * Get home URL of a specific lang
 *
 * @since 2.2
 *
 * @param string $lang (default: '') The language code
 * @return string $url
 */
function get_rocket_i18n_home_url( $lang = '' ) {
	$url = home_url();
	if ( ! rocket_has_i18n() ) {
		return $url;
	}

	if ( rocket_is_plugin_active('sitepress-multilingual-cms/sitepress.php') ) {
		$url = $GLOBALS['sitepress']->language_url( $lang );
	} elseif ( rocket_is_plugin_active( 'qtranslate/qtranslate.php' ) ) {
		$url = qtrans_convertURL( home_url(), $lang, true );
	} elseif ( rocket_is_plugin_active( 'polylang/polylang.php' ) ) {
		$url = pll_home_url( $lang );
	}

	return $url;
}

/**
 * Get all translated path of a specific post with ID.
 *
 * @since	2.4
 *
 * @param 	int 	$post_id	Post ID
 * @param 	string 	$post_type 	Post Type
 * @param 	string 	$regex 		Regex to include at the end
 * @return 	array	$urls
 */
function get_rocket_i18n_translated_post_urls( $post_id, $post_type = 'page', $regex = null ) {
	$urls  = array();
	$path  = parse_url( get_permalink( $post_id ), PHP_URL_PATH );
	$langs = get_rocket_i18n_code();
	
	if ( empty( $path ) ) {
		return $urls;
	}
	
	// WPML
	if ( rocket_is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
		foreach( $langs as $lang ) {
			$urls[] = parse_url( get_permalink( icl_object_id( $post_id, $post_type, true, $lang ) ), PHP_URL_PATH ) . $regex;
		}
	}

	// qTranslate
	if ( rocket_is_plugin_active( 'qtranslate/qtranslate.php' ) ) {
		$langs  = $GLOBALS['q_config']['enabled_languages'];
		$langs  = array_diff( $langs, array( $GLOBALS['q_config']['default_language'] ) );
		$url    = get_permalink( $post_id );
		$urls[] = parse_url( get_permalink( $post_id ), PHP_URL_PATH ) . $regex;

		foreach( $langs as $lang ) {
			$urls[] = parse_url( qtrans_convertURL( $url, $lang, true ), PHP_URL_PATH ) . $regex;
		}
	}

	// Polylang
	if ( rocket_is_plugin_active( 'polylang/polylang.php' ) && is_object( $GLOBALS['polylang']->model ) && $translations = $GLOBALS['polylang']->model->get_translations( 'page', $post_id ) ) {
		foreach ( $translations as $post_id ) {
			$urls[] = parse_url( get_permalink( $post_id ), PHP_URL_PATH ) . $regex;
		}
	}
	
	if ( trim( $path, '/' ) != '' ) {
		$urls[] = $path . $regex;	
	}
	
	$urls = array_unique( $urls );

	return $urls;
}

/**
 * Get cart & checkout path with their translations to automatically exclude them to the cache.
 *
 * @since 2.4
 *
 * @return array $urls
 */
function get_rocket_ecommerce_exclude_pages() {
	$urls = array();
	
	// WooCommerce
	if ( function_exists( 'WC' ) && function_exists( 'wc_get_page_id' ) ) {
		if( wc_get_page_id( 'checkout' ) && wc_get_page_id( 'checkout' ) != '-1' ) {
			$checkout_urls = get_rocket_i18n_translated_post_urls( wc_get_page_id( 'checkout' ), 'page', '(.*)' );
			$urls = array_merge( $urls, $checkout_urls );
		}

		if ( wc_get_page_id( 'cart' ) && wc_get_page_id( 'cart' ) != '-1' ) {
			$cart_urls = get_rocket_i18n_translated_post_urls( wc_get_page_id( 'cart' ) );
			$urls = array_merge( $urls, $cart_urls );
		}
	}
	
	// Easy Digital Downloads
	$edd_settings = get_option( 'edd_settings' );
	if ( function_exists( 'EDD' ) && isset( $edd_settings['purchase_page'] ) ) {
		$checkout_urls = get_rocket_i18n_translated_post_urls( $edd_settings['purchase_page'], 'page', '(.*)' );
		$urls = array_merge( $urls, $checkout_urls );
	}
	
	// iThemes Exchange
	if ( function_exists( 'it_exchange_get_page_type' ) && function_exists( 'it_exchange_get_page_url' ) ) {
		$pages = array(
			'purchases',
			'confirmation'
		);
		
		foreach( $pages as $page ) {
			if ( it_exchange_get_page_type( $page ) == 'wordpress' ) {
				$exchange_urls = get_rocket_i18n_translated_post_urls( it_exchange_get_page_wpid( $page ) );
			} else {
				$exchange_urls = array( parse_url( it_exchange_get_page_url( $page ), PHP_URL_PATH ) );
			}
			
			$urls = array_merge( $urls, $exchange_urls );
		}
	}
	
	// Jigoshop
	if ( defined( 'JIGOSHOP_VERSION' ) && function_exists( 'jigoshop_get_page_id' ) ) {
		if ( jigoshop_get_page_id( 'checkout' ) && jigoshop_get_page_id( 'checkout' ) != '-1' ) {
			$checkout_urls = get_rocket_i18n_translated_post_urls( jigoshop_get_page_id( 'checkout' ), 'page', '(.*)' );
			$urls = array_merge( $urls, $checkout_urls );
		}
		if ( jigoshop_get_page_id( 'cart' ) && jigoshop_get_page_id( 'cart' ) != '-1' ) {
			$cart_urls = get_rocket_i18n_translated_post_urls( jigoshop_get_page_id( 'cart' ) );
			$urls = array_merge( $urls, $cart_urls );
		}
	}
	
	// WP Shop
	if ( defined( 'WPSHOP_VERSION' ) && class_exists( 'wpshop_tools' ) && method_exists( 'wpshop_tools','get_page_id' ) ) {	
		$pages = array(
			'wpshop_cart_page_id',
			'wpshop_checkout_page_id',
			'wpshop_payment_return_page_id',
			'wpshop_payment_return_nok_page_id'
		);
		
		foreach( $pages as $page ) {
			if ( $page_id = wpshop_tools::get_page_id( get_option( $page ) ) ) {
				$urls = array_merge( $urls, get_rocket_i18n_translated_post_urls( $page_id ) );
			}
		}
	}

	return $urls;
}