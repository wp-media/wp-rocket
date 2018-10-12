<?php
namespace WP_Rocket\Subscriber\Third_Party\Plugins\Ecommerce;

use WP_Rocket\Event_Management\Event_Manager;
use WP_Rocket\Event_Management\Event_Manager_Aware_Subscriber_Interface;
use WooCommerce;
use WC_API;

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * WooCommerce compatibility
 *
 * @since 3.1
 * @author Remy Perona
 */
class WooCommerce_Subscriber implements Event_Manager_Aware_Subscriber_Interface {
	use \WP_Rocket\Traits\Config_Updater;

	/**
	 * The WordPress Event Manager
	 *
	 * @var Event_Manager;
	 */
	protected $event_manager;

	/**
	 * {@inheritdoc}
	 */
	public function set_event_manager( Event_Manager $event_manager ) {
		$this->event_manager = $event_manager;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_subscribed_events() {
		$events = array(
			'activate_woocommerce/woocommerce.php'   => [ 'activate_woocommerce', 11 ],
			'deactivate_woocommerce/woocommerce.php' => [ 'deactivate_woocommerce', 11 ],
		);

		if ( class_exists( 'WooCommerce' ) ) {
			$events['update_option_woocommerce_cart_page_id']             = [ 'after_update_single_option', 10, 2 ];
			$events['update_option_woocommerce_checkout_page_id']         = [ 'after_update_single_option', 10, 2 ];
			$events['update_option_woocommerce_myaccount_page_id']        = [ 'after_update_single_option', 10, 2 ];
			$events['update_option_woocommerce_default_customer_address'] = [ 'after_update_single_option', 10, 2 ];

			$events['shutdown']                            = 'maybe_update_config';
			$events['woocommerce_save_product_variation']  = 'clean_cache_after_woocommerce_save_product_variation';
			$events['transition_post_status']              = [ 'maybe_exclude_page', 10, 3 ];
			$events['rocket_cache_reject_uri']             = [
				[ 'exclude_pages' ],
			];
			$events['rocket_cache_query_strings']          = 'cache_geolocation_query_string';

			/**
			 * Filters activation of WooCommerce empty cart caching
			 *
			 * @since 3.1
			 * @author Remy Perona
			 *
			 * @param bool true to activate, false to deactivate.
			 */
			if ( apply_filters( 'rocket_cache_wc_empty_cart', true ) ) {
				$events['plugins_loaded']    = [ 'serve_cache_empty_cart', 11 ];
				$events['template_redirect'] = [ 'cache_empty_cart', -1 ];
				$events['switch_theme']      = 'delete_cache_empty_cart';
			}
		}

		if ( class_exists( 'WC_API' ) ) {
			$events['rocket_cache_reject_uri'][] = [ 'exclude_wc_rest_api' ];
		}

		return $events;
	}

	/**
	 * Add query string to exclusion when activating the plugin
	 *
	 * @since 2.8.6
	 * @author Rémy Perona
	 */
	public function activate_woocommerce() {
		$this->event_manager->add_callback( 'rocket_cache_reject_uri', [ $this, 'exclude_pages' ] );
		$this->event_manager->add_callback( 'rocket_cache_reject_uri', [ $this, 'exclude_wc_rest_api' ] );
		$this->event_manager->add_callback( 'rocket_cache_query_strings', [ $this, 'cache_geolocation_query_string' ] );

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
		$this->event_manager->remove_callback( 'rocket_cache_reject_uri', [ $this, 'exclude_pages' ] );
		$this->event_manager->remove_callback( 'rocket_cache_reject_uri', [ $this, 'exclude_wc_rest_api' ] );
		$this->event_manager->remove_callback( 'rocket_cache_query_strings', [ $this, 'cache_geolocation_query_string' ] );

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
			die();
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
