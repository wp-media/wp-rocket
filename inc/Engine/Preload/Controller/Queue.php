<?php

namespace WP_Rocket\Engine\Preload\Controller;

use WP_Rocket\Engine\Common\Queue\AbstractASQueue;

class Queue extends AbstractASQueue {
	/**
	 * Queue group.
	 *
	 * @var string
	 */
	protected $group = 'rocket-preload';

	/**
	 * Add Async job with DB row ID.
	 *
	 * @param string $sitemap_url DB row ID.
	 *
	 * @return string
	 */
	public function add_job_preload_job_parse_sitemap_async( string $sitemap_url ) {
		return $this->add_async(
			'rocket_preload_job_parse_sitemap',
			[
				$sitemap_url,
			]
		);
	}

	/**
	 * Add Async job with DB row ID.
	 *
	 * @param string $sitemap_url DB row ID.
	 *
	 * @return string
	 */
	public function add_job_preload_job_preload_url_async( string $sitemap_url ) {
		return $this->add_async(
			'rocket_preload_job_preload_url',
			[
				$sitemap_url,
			]
		);
	}
}
