<?php

namespace WP_Rocket\Engine\Preload\Controller;

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
	 * Instantiate the class.
	 *
	 * @param Queue $queue Queue group.
	 */
	public function __construct( Queue $queue ) {
		$this->queue = $queue;
	}

	/**
	 * Load the initial sitemap to the queue.
	 */
	public function load_initial_sitemap() {
		/**
		 * Filter sitemaps URL.
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
		$this->queue->add_job_preload_job_check_finished_async();
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
}
