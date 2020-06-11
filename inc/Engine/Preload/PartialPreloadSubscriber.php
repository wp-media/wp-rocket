<?php

namespace WP_Rocket\Engine\Preload;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Admin\Options_Data;

/**
 * Subscriber for the partial preload
 *
 * @since 3.2
 * @author Remy Perona
 */
class PartialPreloadSubscriber implements Subscriber_Interface {
	/**
	 * Partial preload process instance
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @var PartialProcess
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
	 * @param PartialProcess $partial Partial preload instance.
	 * @param Options_Data   $options Options instance.
	 */
	public function __construct( PartialProcess $partial, Options_Data $options ) {
		$this->partial_preload = $partial;
		$this->options         = $options;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.2
	 * @author Remy Perona
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'after_rocket_clean_post'            => [ 'preload_after_clean_post', 10, 3 ],
			'after_rocket_clean_term'            => [ 'preload_after_clean_term', 10, 3 ],
			'rocket_after_automatic_cache_purge' => 'preload_after_automatic_cache_purge',
			'shutdown'                           => [ 'maybe_dispatch', PHP_INT_MAX ],
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
		$purge_author = [ get_author_posts_url( $post->post_author ) ];

		// Remove author page from preload cache.
		$purge_urls = array_diff( $purge_urls, $purge_author );

		$purge_urls = array_filter( $purge_urls );

		$this->urls = array_merge( $this->urls, $purge_urls );
	}

	/**
	 * Pushes URLs to preload to the queue after cache directories are purged.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param array $deleted {
	 *     An array of arrays, described like: {.
	 *         @type string $home_url  The home URL.
	 *         @type string $home_path Path to home.
	 *         @type bool   $logged_in True if the home path corresponds to a logged in user’s folder.
	 *         @type array  $files     A list of paths of files that have been deleted.
	 *     }
	 * }
	 */
	public function preload_after_automatic_cache_purge( $deleted ) {
		if ( ! $deleted || ! $this->options->get( 'manual_preload' ) ) {
			return;
		}

		foreach ( $deleted as $data ) {
			if ( $data['logged_in'] ) {
				// Logged in user: no need to preload those since we would need the corresponding cookies.
				continue;
			}
			foreach ( $data['files'] as $file_path ) {
				if ( strpos( $file_path, '#' ) ) {
					// URL with query string.
					$file_path = preg_replace( '/#/', '?', $file_path, 1 );
				} else {
					$file_path         = untrailingslashit( $file_path );
					$data['home_path'] = untrailingslashit( $data['home_path'] );
					$data['home_url']  = untrailingslashit( $data['home_url'] );
					if ( '/' === substr( get_option( 'permalink_structure' ), -1 ) ) {
						$file_path         .= '/';
						$data['home_path'] .= '/';
						$data['home_url']  .= '/';
					}
				}

				$this->urls[] = str_replace( $data['home_path'], $data['home_url'], $file_path );
			}
		}
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
		if ( wp_doing_ajax() ) {
			return;
		}

		if ( empty( $this->urls ) ) {
			return;
		}

		$this->urls = array_unique( $this->urls );

		/**
		 * Limit the number of URLs to preload.
		 * The value may change in the future, depending on the results.
		 *
		 * @since  3.4
		 * @author Grégory Viguier
		 *
		 * @param int $limit Maximum number of URLs to preload at once.
		 */
		$limit  = (int) apply_filters( 'rocket_preload_limit_number', 100 );
		$count  = 0;
		$mobile = $this->partial_preload->is_mobile_preload_enabled();

		foreach ( $this->urls as $url ) {
			$path = wp_parse_url( $url, PHP_URL_PATH );

			if ( isset( $path ) && preg_match( '#^(' . \get_rocket_cache_reject_uri() . ')$#', $path ) ) {
				continue;
			}

			$this->partial_preload->push_to_queue( $url );

			if ( $mobile ) {
				$this->partial_preload->push_to_queue(
					[
						'url'    => $url,
						'mobile' => true,
					]
				);
			}

			++$count;

			if ( $count >= $limit ) {
				break;
			}
		}

		$this->partial_preload->save()->dispatch();
	}
}
