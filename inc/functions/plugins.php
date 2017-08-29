<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

/**
 * Check whether the plugin is active by checking the active_plugins list.
 *
 * @since 1.3.0
 *
 * @source wp-admin/includes/plugin.php
 *
 * @param string $plugin Plugin name.
 * @return bool True if active, false otherwise
 */
function rocket_is_plugin_active( $plugin ) {
	return in_array( $plugin, (array) get_option( 'active_plugins', array() ), true ) || rocket_is_plugin_active_for_network( $plugin );
}

/**
 * Check whether the plugin is active for the entire network.
 *
 * @since 1.3.0
 *
 * @source wp-admin/includes/plugin.php
 *
 * @param string $plugin Plugin name.
 * @return bool True if active, false otherwise
 */
function rocket_is_plugin_active_for_network( $plugin ) {
	if ( ! is_multisite() ) {
		return false;
	}

	$plugins = get_site_option( 'active_sitewide_plugins' );
	if ( isset( $plugins[ $plugin ] ) ) {
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
		$url    = home_url( '/?vhp-regex' );
		$p      = parse_url( $url );
		$path   = '';
		$pregex = '.*';

		// Build a varniship.
		if ( defined( 'VHP_VARNISH_IP' ) && VHP_VARNISH_IP ) {
			$varniship = VHP_VARNISH_IP;
		} else {
			$varniship = get_option( 'vhp_varnish_ip' );
		}

		if ( isset( $p['path'] ) ) {
			$path = $p['path'];
		}

		$schema = apply_filters( 'varnish_http_purge_schema', 'http://' );

		// If we made varniship, let it sail.
		if ( ! empty( $varniship ) ) {
			$purgeme = $schema . $varniship . $path . $pregex;
		} else {
			$purgeme = $schema . $p['host'] . $path . $pregex;
		}

		wp_remote_request(
			$purgeme,
			array(
				'method'   => 'PURGE',
				'blocking' => false,
				'headers'  => array(
					'host'           => $p['host'],
					'X-Purge-Method' => 'regex',
				),
			)
		);

		do_action( 'after_purge_url', $url, $purgeme );
	}
}

/**
 * Call the cache server to purge the cache with Pagely hosting.
 *
 * @since 2.5.7
 *
 * @return void
 */
function rocket_clean_pagely() {
	if ( class_exists( 'HCSVarnish' ) ) {
		$varnish = new HCSVarnish();
		$varnish->HCSVarnishPurgeAll();
	}
}

/**
 * Call the cache server to purge the cache with Pagely hosting.
 *
 * @since 2.6
 *
 * @return void
 */
function rocket_clean_pressidium() {
	if ( class_exists( 'Ninukis_Plugin' ) ) {
		$plugin = Ninukis_Plugin::get_instance();
		$plugin->purgeAllCaches();
	}
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

	// WooCommerce.
	if ( function_exists( 'WC' ) && function_exists( 'wc_get_page_id' ) ) {
		if ( wc_get_page_id( 'checkout' ) && wc_get_page_id( 'checkout' ) !== -1 && wc_get_page_id( 'checkout' ) !== (int) get_option( 'page_on_front' ) ) {
			$checkout_urls = get_rocket_i18n_translated_post_urls( wc_get_page_id( 'checkout' ), 'page', '(.*)' );
			$urls = array_merge( $urls, $checkout_urls );
		}

		if ( wc_get_page_id( 'cart' ) && wc_get_page_id( 'cart' ) !== -1 && wc_get_page_id( 'cart' ) !== (int) get_option( 'page_on_front' ) ) {
			$cart_urls = get_rocket_i18n_translated_post_urls( wc_get_page_id( 'cart' ) );
			$urls = array_merge( $urls, $cart_urls );
		}

		if ( wc_get_page_id( 'myaccount' ) && wc_get_page_id( 'myaccount' ) !== -1 && wc_get_page_id( 'myaccount' ) !== (int) get_option( 'page_on_front' ) ) {
			$cart_urls = get_rocket_i18n_translated_post_urls( wc_get_page_id( 'myaccount' ), 'page', '(.*)' );
			$urls = array_merge( $urls, $cart_urls );
		}
	}

	// Easy Digital Downloads.
	$edd_settings = get_option( 'edd_settings' );
	if ( function_exists( 'EDD' ) && ! empty( $edd_settings['purchase_page'] ) ) {
		$checkout_urls = get_rocket_i18n_translated_post_urls( $edd_settings['purchase_page'], 'page', '(.*)' );
		$urls = array_merge( $urls, $checkout_urls );
	}

	if ( function_exists( 'EDD' ) && ! empty( $edd_settings['success_page'] ) ) {
		$success_urls  = get_rocket_i18n_translated_post_urls( $edd_settings['success_page'], 'page', '(.*)' );
		$urls = array_merge( $urls, $success_urls );
	}

	// iThemes Exchange.
	if ( function_exists( 'it_exchange_get_page_type' ) && function_exists( 'it_exchange_get_page_url' ) ) {
		$pages = array(
			'purchases',
			'confirmation',
			'account',
			'profile',
			'downloads',
			'purchases',
			'log-in',
			'log-out',
		);

		foreach ( $pages as $page ) {
			if ( it_exchange_get_page_type( $page ) === 'wordpress' ) {
				$exchange_urls = get_rocket_i18n_translated_post_urls( it_exchange_get_page_wpid( $page ) );
			} else {
				$exchange_urls = array( parse_url( it_exchange_get_page_url( $page ), PHP_URL_PATH ) );
			}

			$urls = array_merge( $urls, $exchange_urls );
		}
	}

	// Jigoshop.
	if ( defined( 'JIGOSHOP_VERSION' ) && function_exists( 'jigoshop_get_page_id' ) ) {
		if ( jigoshop_get_page_id( 'checkout' ) && jigoshop_get_page_id( 'checkout' ) !== -1 ) {
			$checkout_urls = get_rocket_i18n_translated_post_urls( jigoshop_get_page_id( 'checkout' ), 'page', '(.*)' );
			$urls = array_merge( $urls, $checkout_urls );
		}

		if ( jigoshop_get_page_id( 'cart' ) && jigoshop_get_page_id( 'cart' ) !== -1 ) {
			$cart_urls = get_rocket_i18n_translated_post_urls( jigoshop_get_page_id( 'cart' ) );
			$urls = array_merge( $urls, $cart_urls );
		}

		if ( jigoshop_get_page_id( 'myaccount' ) && jigoshop_get_page_id( 'myaccount' ) !== -1 ) {
			$cart_urls = get_rocket_i18n_translated_post_urls( jigoshop_get_page_id( 'myaccount' ), 'page', '(.*)' );
			$urls = array_merge( $urls, $cart_urls );
		}
	}

	// WP Shop.
	if ( defined( 'WPSHOP_VERSION' ) && class_exists( 'wpshop_tools' ) && method_exists( 'wpshop_tools','get_page_id' ) ) {
		$pages = array(
			'wpshop_cart_page_id',
			'wpshop_checkout_page_id',
			'wpshop_payment_return_page_id',
			'wpshop_payment_return_nok_page_id',
			'wpshop_myaccount_page_id',
		);

		foreach ( $pages as $page ) {
			if ( $page_id = wpshop_tools::get_page_id( get_option( $page ) ) ) {
				$urls = array_merge( $urls, get_rocket_i18n_translated_post_urls( $page_id ) );
			}
		}
	}

	return $urls;
}

/**
 * Get hide login pages to automatically exclude them to the cache.
 *
 * @since 2.6
 *
 * @return array $urls
 */
function get_rocket_logins_exclude_pages() {
	$urls = array();

	// WPS Hide Login - Don't return its slug on deactivation.
	if ( class_exists( 'WPS_Hide_Login' ) && 'deactivate_wps-hide-login/wps-hide-login.php' !== current_filter() ) {
		$urls[] = rocket_clean_exclude_file( home_url( trailingslashit( get_option( 'whl_page' ) ) ) );
	}

	return $urls;
}
