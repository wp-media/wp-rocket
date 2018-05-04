<?php
namespace WP_Rocket\Third_Party\Plugins\Ecommerce;

use WooCommerce;
use WC_API;

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * WooCommerce compatibility
 *
 * @since 3.1
 * @author Remy Perona
 */
class WooCommerce_Compatibility {
	/**
	 * Initializes hooks
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'activate_woocommerce/woocommerce.php', [ $this, 'activate_woocommerce' ], 11 );
		add_action( 'deactivate_woocommerce/woocommerce.php', [ $this, 'deactivate_woocommerce' ], 11 );

		if ( class_exists( 'WooCommerce' ) ) {
			add_action( 'update_option_woocommerce_cart_page_id', 'rocket_after_update_single_options', 10, 2 );
			add_action( 'update_option_woocommerce_checkout_page_id', 'rocket_after_update_single_options', 10, 2 );
			add_action( 'update_option_woocommerce_myaccount_page_id', 'rocket_after_update_single_options', 10, 2 );
			add_action( 'update_option_woocommerce_default_customer_address', 'rocket_after_update_single_options', 10, 2 );
			add_action( 'woocommerce_save_product_variation', [ $this, 'clean_cache_after_woocommerce_save_product_variation' ] );
			add_action( 'transition_post_status', [ $this, 'maybe_exclude_page' ], 10, 3 );
			add_filter( 'rocket_cache_reject_uri', [ $this, 'exclude_pages' ] );
			add_filter( 'rocket_cache_query_strings', [ $this, 'cache_geolocation_query_string' ] );
			// Prevent conflict with WooCommerce when clean_post_cache is called.
			add_filter( 'delete_transient_wc_products_onsale', 'wp_suspend_cache_invalidation' );

			/**
			 * Filters activation of WooCommerce empty cart caching
			 *
			 * @since 3.1
			 * @author Remy Perona
			 *
			 * @param bool true to activate, false to deactivate.
			 */
			if ( apply_filters( 'rocket_cache_wc_empty_cart', true ) ) {
				add_action( 'plugins_loaded', [ $this, 'serve_cache_empty_cart' ], 11 );
				add_action( 'template_redirect', [ $this, 'cache_empty_cart' ], -1 );
				add_action( 'switch_theme', [ $this, 'delete_cache_empty_cart' ] );
			}
		}

		if ( class_exists( 'WC_API' ) ) {
			add_filter( 'rocket_cache_reject_uri', [ $this, 'exclude_wc_rest_api' ] );
		}
	}

	/**
	 * Add query string to exclusion when activating the plugin
	 *
	 * @since 2.8.6
	 * @author Rémy Perona
	 */
	public function activate_woocommerce() {
		add_filter( 'rocket_cache_reject_uri', [ $this, 'exclude_pages' ] );
		add_filter( 'rocket_cache_reject_uri', [ $this, 'exclude_wc_rest_api' ] );
		add_filter( 'rocket_cache_query_strings', [ $this, 'cache_geolocation_query_string' ] );

		// Update .htaccess file rules.
		flush_rocket_htaccess();

		// Regenerate the config file.
		rocket_generate_config_file();
	}


	/**
	 * Remove query string from exclusion when deactivating the plugin
	 *
	 * @since 2.8.6
	 * @author Rémy Perona
	 */
	public function deactivate_woocommerce() {
		remove_filter( 'rocket_cache_reject_uri', [ $this, 'exclude_pages' ] );
		remove_filter( 'rocket_cache_reject_uri', [ $this, 'exclude_wc_rest_api' ] );
		remove_filter( 'rocket_cache_query_strings', [ $this, 'cache_geolocation_query_string' ] );

		// Update .htaccess file rules.
		flush_rocket_htaccess();

		// Regenerate the config file.
		rocket_generate_config_file();
	}

	/**
	 * Clean product cache on variation update
	 *
	 * @since 2.9
	 * @author Remy Perona
	 *
	 * @param int $variation_id ID of the variation.
	 * @return bool
	 */
	public function clean_cache_after_woocommerce_save_product_variation( $variation_id ) {
		$product_id = wp_get_post_parent_id( $variation_id );

		if ( ! $product_id ) {
			return false;
		}

		rocket_clean_post( $product_id );

		return true;
	}

	/**
	 * Maybe regenerate the htaccess & config file if a WooCommerce page is published
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string  $new_status New post status.
	 * @param string  $old_status Old post status.
	 * @param WP_Post $post       Post object.
	 * @return bool
	 */
	public function maybe_exclude_page( $new_status, $old_status, $post ) {
		if ( 'publish' === $old_status || 'publish' !== $new_status ) {
			return false;
		}

		if ( ! function_exists( 'wc_get_page_id' ) ) {
			return false;
		}

		if ( wc_get_page_id( 'checkout' ) !== $post->ID && wc_get_page_id( 'cart' ) !== $post->ID && wc_get_page_id( 'myaccount' ) !== $post->ID ) {
			return false;
		}

		// Update .htaccess file rules.
		flush_rocket_htaccess();

		// Regenerate the config file.
		rocket_generate_config_file();

		return true;
	}

	/**
	 * Exclude WooCommerce cart, checkout and account pages from caching
	 *
	 * @since 2.11 Moved to 3rd party
	 * @since 2.4
	 *
	 * @param array $urls An array of excluded pages.
	 * @return array Updated array of excluded pages
	 */
	public function exclude_pages( $urls ) {
		if ( ! function_exists( 'wc_get_page_id' ) ) {
			return $urls;
		}

		$checkout_urls = $this->exclude_page( wc_get_page_id( 'checkout' ), 'page', '(.*)' );
		$cart_urls     = $this->exclude_page( wc_get_page_id( 'cart' ) );
		$account_urls  = $this->exclude_page( wc_get_page_id( 'myaccount' ), 'page', '(.*)' );

		return array_merge( $urls, $checkout_urls, $cart_urls, $account_urls );
	}

	/**
	 * Excludes WooCommerce checkout page from cache
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param int    $page_id   ID of page to exclude.
	 * @param string $post_type Post type of the page.
	 * @param string $pattern   Pattern to use for the exclusion.
	 * @return array
	 */
	private function exclude_page( $page_id, $post_type = 'page', $pattern = '' ) {
		$urls = [];

		if ( $page_id <= 0 || (int) get_option( 'page_on_front' ) === $page_id ) {
			return $urls;
		}

		if ( 'publish' !== get_post_status( $page_id ) ) {
			return $urls;
		}

		$urls = get_rocket_i18n_translated_post_urls( $page_id, $post_type, $pattern );

		return $urls;
	}

	/**
	 * Automatically cache v query string when WC geolocation with cache compatibility option is active
	 *
	 * @since 2.8.6
	 * @author Rémy Perona
	 *
	 * @param array $query_strings list of query strings to cache.
	 * @return array Updated list of query strings to cache
	 */
	public function cache_geolocation_query_string( $query_strings ) {
		if ( 'geolocation_ajax' !== get_option( 'woocommerce_default_customer_address' ) ) {
			return $query_strings;
		}

		$query_strings[] = 'v';

		return $query_strings;
	}

	/**
	 * Returns WooCommerce API endpoint
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return string
	 */
	private function get_wc_api_endpoint() {
		return home_url( '/wc-api/v(.*)' );
	}

	/**
	 * Exclude WooCommerce REST API URL from cache
	 *
	 * @since 2.6.5
	 *
	 * @param array $urls URLs to exclude from cache.
	 * @return array Updated list of URLs to exclude from cache
	 */
	public function exclude_wc_rest_api( $urls ) {
		/**
		 * By default, don't cache the WooCommerce REST API.
		 *
		 * @since 2.6.5
		 *
		 * @param bool false will force to cache the WooCommerce REST API
		 */
		if ( apply_filters( 'rocket_cache_reject_wc_rest_api', true ) ) {
			$urls[] = rocket_clean_exclude_file( $this->get_wc_api_endpoint() );
		}

		return $urls;
	}

	/**
	 * Serves the empty cart cache
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function serve_cache_empty_cart() {
		if ( ! $this->is_get_refreshed_fragments() ) {
			return;
		}

		$cart = $this->get_cache_empty_cart();

		if ( false !== $cart ) {
			@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
			echo $cart;
			die;
		}
	}

	/**
	 * Creates the empty cart cache
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function cache_empty_cart() {
		if ( ! $this->is_get_refreshed_fragments() ) {
			return;
		}

		$cart = $this->get_cache_empty_cart();

		if ( false !== $cart ) {
			return;
		}

		ob_start( [ $this, 'save_cache_empty_cart' ] );
	}

	/**
	 * Gets the empty cart cache
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return string
	 */
	private function get_cache_empty_cart() {
		return get_transient( 'rocket_get_refreshed_fragments_cache' );
	}

	/**
	 * Saves the empty cart JSON in a transient
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $content Current buffer content.
	 * @return string
	 */
	private function save_cache_empty_cart( $content ) {
		set_transient( 'rocket_get_refreshed_fragments_cache', $content, 7 * DAY_IN_SECONDS );

		return $content;
	}

	/**
	 * Checks if the request is for get_refreshed_fragments and the cart is empty
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return boolean
	 */
	private function is_get_refreshed_fragments() {
		if ( ! isset( $_GET['wc-ajax'] ) ) { // WPCS: CSRF ok.
			return false;
		}

		if ( 'get_refreshed_fragments' !== $_GET['wc-ajax'] ) { // WPCS: CSRF ok.
			return false;
		}

		if ( ! empty( $_COOKIE['woocommerce_cart_hash'] ) ) {
			return false;
		}

		if ( ! empty( $_COOKIE['woocommerce_items_in_cart'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Deletes the empty cart cache
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function delete_cache_empty_cart() {
		delete_transient( 'rocket_get_refreshed_fragments_cache' );
	}
}
