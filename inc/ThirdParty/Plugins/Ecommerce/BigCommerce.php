<?php
namespace WP_Rocket\ThirdParty\Plugins\Ecommerce;

use WP_Rocket\Event_Management\Event_Manager;
use WP_Rocket\Event_Management\Event_Manager_Aware_Subscriber_Interface;
use WP_Rocket\Traits\Config_Updater;

/**
 * BigCommerce compatibility subscriber
 *
 * @since 3.3.7
 */
class BigCommerce implements Event_Manager_Aware_Subscriber_Interface {
	use Config_Updater;

	/**
	 * The WordPress Event Manager
	 *
	 * @var Event_Manager;
	 */
	protected $event_manager;

	/**
	 * {@inheritdoc}
	 *
	 * @param Event_Manager $event_manager The WordPress Event Manager.
	 */
	public function set_event_manager( Event_Manager $event_manager ) {
		$this->event_manager = $event_manager;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_subscribed_events() {
		$events = [
			'activate_bigcommerce/bigcommerce.php'   => [ 'activate_bigcommerce', 11 ],
			'deactivate_bigcommerce/bigcommerce.php' => [ 'deactivate_bigcommerce', 11 ],
		];

		if ( function_exists( 'bigcommerce_init' ) ) {
			$events['update_option_bigcommerce_login_page_id']    = [ 'after_update_single_option', 10, 2 ];
			$events['update_option_bigcommerce_account_page_id']  = [ 'after_update_single_option', 10, 2 ];
			$events['update_option_bigcommerce_address_page_id']  = [ 'after_update_single_option', 10, 2 ];
			$events['update_option_bigcommerce_orders_page_id']   = [ 'after_update_single_option', 10, 2 ];
			$events['update_option_bigcommerce_cart_page_id']     = [ 'after_update_single_option', 10, 2 ];
			$events['update_option_bigcommerce_checkout_page_id'] = [ 'after_update_single_option', 10, 2 ];

			$events['shutdown']                = 'maybe_update_config';
			$events['transition_post_status']  = [ 'maybe_exclude_page', 10, 3 ];
			$events['rocket_cache_reject_uri'] = [
				[ 'exclude_pages' ],
			];
		}

		return $events;
	}

	/**
	 * Add exclusions when activating the BigCommerce plugin
	 *
	 * @since 3.3.7
	 */
	public function activate_bigcommerce() {
		$this->event_manager->add_callback( 'rocket_cache_reject_uri', [ $this, 'exclude_pages' ] );

		// Update .htaccess file rules.
		flush_rocket_htaccess();

		// Regenerate the config file.
		rocket_generate_config_file();
	}

	/**
	 * Remove exclusions when deactivating the BigCommerce plugin
	 *
	 * @since 3.3.7
	 */
	public function deactivate_bigcommerce() {
		$this->event_manager->remove_callback( 'rocket_cache_reject_uri', [ $this, 'exclude_pages' ] );

		// Update .htaccess file rules.
		flush_rocket_htaccess();

		// Regenerate the config file.
		rocket_generate_config_file();
	}

	/**
	 * Maybe regenerate the htaccess & config file if a BigCommerce page is published
	 *
	 * @since 3.3.7
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

		if ( get_option( 'bigcommerce_login_page_id' ) !== $post->ID && get_option( 'bigcommerce_account_page_id' ) !== $post->ID && get_option( 'bigcommerce_address_page_id' ) !== $post->ID && get_option( 'bigcommerce_orders_page_id' ) !== $post->ID && get_option( 'bigcommerce_cart_page_id' ) !== $post->ID && get_option( 'bigcommerce_checkout_page_id' ) !== $post->ID ) {
			return false;
		}

		// Update .htaccess file rules.
		flush_rocket_htaccess();

		// Regenerate the config file.
		rocket_generate_config_file();

		return true;
	}

	/**
	 * Exclude BigCommerce login, cart, checkout, account, address and orders pages from caching
	 *
	 * @since 3.3.7
	 *
	 * @param array $urls An array of excluded pages.
	 * @return array
	 */
	public function exclude_pages( $urls ) {
		$checkout_urls = $this->exclude_page( get_option( 'bigcommerce_checkout_page_id' ) );
		$cart_urls     = $this->exclude_page( get_option( 'bigcommerce_cart_page_id' ) );
		$account_urls  = $this->exclude_page( get_option( 'bigcommerce_account_page_id' ) );
		$login_urls    = $this->exclude_page( get_option( 'bigcommerce_login_page_id' ) );
		$address_urls  = $this->exclude_page( get_option( 'bigcommerce_address_page_id' ) );
		$orders_urls   = $this->exclude_page( get_option( 'bigcommerce_orders_page_id' ) );

		return array_merge( $urls, $checkout_urls, $cart_urls, $account_urls, $login_urls, $address_urls, $orders_urls );
	}

	/**
	 * Excludes BigCommerce checkout page from cache
	 *
	 * @since 3.3.7
	 *
	 * @param int    $page_id   ID of page to exclude.
	 * @param string $post_type Post type of the page.
	 * @param string $pattern   Pattern to use for the exclusion.
	 * @return array
	 */
	private function exclude_page( $page_id, $post_type = 'page', $pattern = '' ) {
		$urls = [];

		if ( ! $page_id ) {
			return $urls;
		}

		if ( $page_id <= 0 || (int) get_option( 'page_on_front' ) === $page_id ) {
			return $urls;
		}

		if ( 'publish' !== get_post_status( $page_id ) ) {
			return $urls;
		}

		$urls = get_rocket_i18n_translated_post_urls( $page_id, $post_type, $pattern );

		return $urls;
	}
}
