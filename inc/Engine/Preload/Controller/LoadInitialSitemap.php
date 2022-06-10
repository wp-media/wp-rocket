<?php

namespace WP_Rocket\Engine\Preload\Controller;

use WP_Rocket\Engine\Preload\Database\Queries\Cache;

/**
 * Controller to load initial sitemap tasks.
 */
class LoadInitialSitemap {

	/**
	 * Queue group.
	 *
	 * @var Queue
	 */
	protected $queue;

	/**
	 * DB query.
	 *
	 * @var Cache
	 */
	protected $query;

	/**
	 * Instantiate the class.
	 *
	 * @param Queue $queue Queue group.
	 * @param Cache $query DB query.
	 */
	public function __construct( Queue $queue, $query ) {
		$this->queue = $queue;
		$this->query = $query;
	}

	/**
	 * Load the initial sitemap to the queue.
	 */
	public function load_initial_sitemap() {
		/**
		 * Filter sitemaps URL.
		 *
		 * @param array Array of sitemaps URL
		 */
		$sitemaps = apply_filters( 'rocket_sitemap_preload_list', [] );
		if ( count( $sitemaps ) > 0 ) {
			$this->add_task_to_queue( $sitemaps );
			return;
		}

		$sitemap = $this->load_wordpress_sitemap();

		if ( ! $sitemap ) {
			return;
		}

		$this->add_task_to_queue( [ $sitemap ] );

		/**
		 * Filter custom preload URL.
		 *
		 * @param array Array of custom preload URL
		 */
		$urls = apply_filters( 'rocket_preload_load_custom_urls', [] );
		$urls = array_filter( $urls );

		foreach ( $urls as $url ) {
			$this->queue->add_job_preload_job_preload_url_async( $url );
		}
	}

	/**
	 * Add initial sitemap tasks.
	 *
	 * @param array $sitemaps sitemap used for creating tasks.
	 */
	protected function add_task_to_queue( array $sitemaps ) {
		set_transient( 'wpr_preload_running', true );

		foreach ( $sitemaps as $sitemap ) {
			$this->queue->add_job_preload_job_parse_sitemap_async( $sitemap );
		}
		$this->queue->add_job_preload_job_check_finished_async();
	}

	/**
	 * Load default WordPress sitemap.
	 *
	 * @return false|string
	 */
	protected function load_wordpress_sitemap() {
		$sitemaps = wp_sitemaps_get_server();

		if ( ! $sitemaps ) {
			return false;
		}

		return $sitemaps->index->get_index_url();
	}

	/**
	 * Cancel the preloading.
	 *
	 * @return void
	 */
	public function cancel_preload() {
		$this->queue->cancel_pending_jobs();
		$this->query->revert_in_progress();
	}
}
