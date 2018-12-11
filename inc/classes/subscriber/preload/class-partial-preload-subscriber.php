<?php
namespace WP_Rocket\Subscriber\Preload;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Preload\Partial_Process;

/**
 * Subscriber for the partial preload
 *
 * @since 3.2
 * @author Remy Perona
 */
class Partial_Preload_Subscriber implements Subscriber_Interface {
	/**
	 * Partial preload process instance
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @var Partial_Process
	 */
	private $partial_preload;

	/**
	 * Options instance
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Stores the URLs to preload
	 *
	 * @since 3.2.1
	 * @author Remy Perona
	 *
	 * @var array
	 */
	private $urls = [];

	/**
	 * Constructor
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @param Partial_Process $partial Partial preload instance.
	 * @param Options_Data    $options Options instance.
	 */
	public function __construct( Partial_Process $partial, Options_Data $options ) {
		$this->partial_preload = $partial;
		$this->options         = $options;
	}

	/**
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		return [
			'after_rocket_clean_post' => [ 'preload_after_clean_post', 10, 3 ],
			'after_rocket_clean_term' => [ 'preload_after_clean_term', 10, 3 ],
			'shutdown'                => [ 'maybe_dispatch', 0 ],
		];
	}

	/**
	 * Pushes URLs to preload to the queue after a post has been updated
	 *
	 * @since 3.2
	 *
	 * @param object $post The post object.
	 * @param array  $purge_urls An array of URLs to clean.
	 * @param string $lang The language to clean.
	 */
	public function preload_after_clean_post( $post, $purge_urls, $lang ) {
		if ( ! $this->options->get( 'manual_preload' ) ) {
			return;
		}

		// Run preload only if post is published.
		if ( 'publish' !== $post->post_status ) {
			return false;
		}

		// Add Homepage URL to $purge_urls for preload.
		array_push( $purge_urls, get_rocket_i18n_home_url( $lang ) );

		// Get the author page.
		$purge_author = array( get_author_posts_url( $post->post_author ) );

		// Get all dates archive page.
		$purge_dates = get_rocket_post_dates_urls( $post->ID );

		// Remove dates archives page and author page to preload cache.
		$purge_urls = array_diff( $purge_urls, $purge_dates, $purge_author );

		$purge_urls = array_filter( $purge_urls );

		$this->urls = array_merge( $this->urls, $purge_urls );
	}

	/**
	 * Pushes URLs to preload to the queue after a term has been updated
	 *
	 * @since 3.2
	 *
	 * @param object $term The term object.
	 * @param array  $purge_urls An array of URLs to clean.
	 * @param string $lang The language to clean.
	 */
	public function preload_after_clean_term( $term, $purge_urls, $lang ) {
		if ( ! $this->options->get( 'manual_preload' ) ) {
			return;
		}

		// Add Homepage URL to $purge_urls for preload.
		array_push( $purge_urls, get_rocket_i18n_home_url( $lang ) );

		$purge_urls = array_filter( $purge_urls );

		$this->urls = array_merge( $this->urls, $purge_urls );
	}

	/**
	 * Starts the partial preload process if there is any URLs saved
	 *
	 * @since 3.2.1
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function maybe_dispatch() {
		if ( ! is_admin() ) {
			return;
		}

		if ( wp_doing_ajax() ) {
			return;
		}

		if ( empty( $this->urls ) ) {
			return;
		}

		$this->urls = array_unique( $this->urls );

		foreach ( $this->urls as $url ) {
			$path = wp_parse_url( $url, PHP_URL_PATH );

			if ( isset( $path ) && preg_match( '#^(' . \get_rocket_cache_reject_uri() . ')$#', $path ) ) {
				continue;
			}

			$this->partial_preload->push_to_queue( $url );
		}

		$this->partial_preload->save()->dispatch();
	}
}
