<?php

namespace WP_Rocket\Engine\Preload\Frontend;

use WP_Rocket\Engine\Preload\Controller\CheckExcludedTrait;
use WP_Rocket\Engine\Preload\Controller\Queue;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Logger\Logger;

class FetchSitemap {
	use CheckExcludedTrait;

	/**
	 * Parse controller.
	 *
	 * @var SitemapParser
	 */
	protected $sitemap_parser;

	/**
	 * Queue instance.
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
	 * Logger instance.
	 *
	 * @var Logger
	 */
	protected $logger;

	/**
	 * Instantiate the class.
	 *
	 * @param SitemapParser $sitemap_parser Parse controller.
	 * @param Queue         $queue Queue instance.
	 * @param Cache         $rocket_cache DB query.
	 * @param Logger        $logger Logger instance.
	 */
	public function __construct( SitemapParser $sitemap_parser, Queue $queue, Cache $rocket_cache, Logger $logger ) {
		$this->sitemap_parser = $sitemap_parser;
		$this->queue          = $queue;
		$this->query          = $rocket_cache;
		$this->logger         = $logger;
	}

	/**
	 * Parse a sitemap.
	 *
	 * @param string $url url from the sitemap.
	 */
	public function parse_sitemap( string $url ) {
		$response = wp_safe_remote_get( $url );

		if ( is_wp_error( $response ) ) {
			$this->logger->error( "Sitemap fetch failed for {$url}: " . $response->get_error_message() );
			return;
		}

		$code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $code ) {
			$this->logger->error( "Sitemap fetch failed for {$url}: unexpected response code {$code}" );
			return;
		}

		$data = wp_remote_retrieve_body( $response );

		if ( ! $data ) {
			$this->logger->error( "Sitemap fetch failed for {$url}: empty response body" );
			return;
		}

		$this->sitemap_parser->set_content( $data );
		$links = $this->sitemap_parser->get_links();

		foreach ( $links as $link ) {
			if ( ! $this->is_excluded_by_filter( $link ) ) {
				$this->query->create_or_nothing(
					[
						'url' => $link,
					]
				);
			}
		}

		$children = $this->sitemap_parser->get_children();

		foreach ( $children as $child ) {
			$this->queue->add_job_preload_job_parse_sitemap_async( $child );
		}
	}
}
