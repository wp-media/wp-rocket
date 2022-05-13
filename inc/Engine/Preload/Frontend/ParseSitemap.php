<?php

namespace WP_Rocket\Engine\Preload\Frontend;

use WP_Rocket\Engine\Preload\Controller\Queue;

class ParseSitemap
{
	/**
	 * @var SitemapParser
	 */
	protected $sitemap_parser;

	/**
	 * @var Queue
	 */
	protected $queue;

	/**
	 * @param SitemapParser $sitemap_parser
	 */
	public function __construct(SitemapParser $sitemap_parser, Queue $queue)
	{
		$this->sitemap_parser = $sitemap_parser;
		$this->queue = $queue;
	}

	/**
	 * Parse a sitemap.
	 *
	 * @param string $url url from the sitemap.
	 */
	public function parse_sitemap(string $url) {
		$response  = wp_remote_get($url);

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return;
		}

		$data = wp_remote_retrieve_body( $response );

		$this->sitemap_parser->set_content($data);
		$links = $this->sitemap_parser->get_links();

		foreach ($links as $link) {
			$this->queue->add_job_preload_job_preload_url_async($link);
		}

		$children = $this->sitemap_parser->get_children();

		foreach ($children as $child) {
			$this->queue->add_job_preload_job_parse_sitemap_async($child);
		}
	}
}
