<?php

namespace WP_Rocket\Engine\Preload\Admin;

use stdClass;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Preload\Controller\ClearCache;

use WP_Rocket\Engine\Common\Queue\PreloadQueueRunner;
use WP_Rocket\Engine\Preload\Controller\Queue;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Logger\Logger;

class Subscriber implements Subscriber_Interface {

	/**
	 * Options instance.
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * Settings instance.
	 *
	 * @var Settings
	 */
	protected $settings;

	/**
	 * Clear cache controller.
	 *
	 * @var ClearCache
	 */
	protected $controller;

	/**
	 * Preload queue.
	 *
	 * @var Queue
	 */
	protected $queue;

	/**
	 * Logger instance.
	 *
	 * @var Logger
	 */
	protected $logger;

	/**
	 * Creates an instance of the class.
	 *
	 * @param Options_Data $options Options instance.
	 * @param Settings     $settings Settings instance.
	 * @param ClearCache   $clear_cache Clear cache controller.
	 * @param Queue        $queue preload queue.
	 * @param Logger       $logger logger instance.
	 */
	public function __construct( Options_Data $options, Settings $settings, ClearCache $clear_cache, Queue $queue, Logger $logger ) {
		$this->options    = $options;
		$this->settings   = $settings;
		$this->controller = $clear_cache;
		$this->queue      = $queue;
		$this->logger     = $logger;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'admin_notices'                      => [
				'maybe_display_preload_notice',
				'maybe_display_as_missed_tables_notice',
			],
			'after_rocket_clean_post'            => [ 'clean_partial_cache', 10, 3 ],
			'after_rocket_clean_term'            => [ 'clean_partial_cache', 10, 3 ],
			'after_rocket_clean_file'            => 'clean_url',
			'rocket_after_clean_terms'           => 'clean_urls',
			'after_rocket_clean_domain'          => 'clean_full_cache',
			'rocket_after_automatic_cache_purge' => 'preload_after_automatic_cache_purge',
			'wp_trash_post'                      => 'delete_post_preload_cache',
			'delete_post'                        => 'delete_post_preload_cache',
			'pre_delete_term'                    => 'delete_term_preload_cache',
		];
	}

	/**
	 * Maybe display the preload notice.
	 *
	 * @return void
	 */
	public function maybe_display_preload_notice() {
		$this->settings->maybe_display_preload_notice();
	}

	/**
	 * Preload after clearing full cache.
	 *
	 * @return void
	 */
	public function clean_full_cache() {
		set_transient( 'wpr_preload_running', true );
		$this->queue->add_job_preload_job_check_finished_async();
		$this->controller->full_clean();
	}

	/**
	 * Preload after clearing some cache.
	 *
	 * @param stdClass $object object modified.
	 * @param array    $urls urls cleaned.
	 * @param string   $lang lang from the website.
	 * @return void
	 */
	public function clean_partial_cache( $object, array $urls, $lang ) {
		// Add Homepage URL to $purge_urls for preload.
		$urls[] = get_rocket_i18n_home_url( $lang );

		$urls = array_filter( $urls );
		$this->controller->partial_clean( $urls );
	}

	/**
	 * Clean the list of urls.
	 *
	 * @param array $urls urls.
	 * @return void
	 */
	public function clean_urls( array $urls ) {

		$this->controller->partial_clean( $urls );
	}

	/**
	 * Clean the url.
	 *
	 * @param string $url url.
	 * @return void
	 */
	public function clean_url( string $url ) {

		$this->controller->partial_clean( [ $url ] );
	}

	/**
	 * Delete URL from a post from the preload.
	 *
	 * @param int $post_id ID from the post.
	 * @return void
	 */
	public function delete_post_preload_cache( $post_id ) {
		if ( ! $this->settings->is_enabled() ) {
			return;
		}

		$url = get_permalink( $post_id );

		if ( false === $url ) {
			return;
		}

		$this->controller->delete_url( $url );
	}

	/**
	 * Delete URL from a term from the preload.
	 *
	 * @param int $term_id ID from the term.
	 * @return void
	 */
	public function delete_term_preload_cache( $term_id ) {
		if ( ! $this->settings->is_enabled() ) {
			return;
		}

		$url = get_term_link( (int) $term_id );

		if ( false === $url ) {
			return;
		}

		$this->controller->delete_url( $url );
	}

	/**
	 * Display a notice when Action Scheduler tables are missing.
	 *
	 * @return void
	 */
	public function maybe_display_as_missed_tables_notice() {
		$this->settings->maybe_display_as_missed_tables_notice();
	}

	/**
	 * Pushes URLs to preload to the queue after cache directories are purged.
	 *
	 * @since  3.4
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

				$this->controller->partial_clean( [ str_replace( $data['home_path'], $data['home_url'], $file_path ) ] );
			}
		}
	}
}
