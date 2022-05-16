<?php

namespace WP_Rocket\Engine\Preload\Frontend;

use SimpleXMLElement;

class SitemapParser {

	/**
	 * XML document to parse.
	 *
	 * @var SimpleXMLElement|false
	 */
	protected $xml;

	/**
	 * Set the content from the sitemap to parse.
	 *
	 * @param string $content content from the sitemap to parse.
	 */
	public function set_content( string $content ) {
		libxml_use_internal_errors( true );

		$this->xml = simplexml_load_string( $content );
	}

	/**
	 * Get links to sitemaps.
	 *
	 * @return array
	 */
	public function get_links(): array {
		$links = [];

		if ( false === $this->xml ) {
			return [];
		}

		$url_count = count( $this->xml->url );

		for ( $i = 0; $i < $url_count; $i++ ) {
			$url = (string) $this->xml->url[ $i ]->loc;
			if ( ! $url ) {
				continue;
			}
			$links [] = $url;
		}

		return $links;
	}

	/**
	 * Get children sitemaps.
	 *
	 * @return array
	 */
	public function get_children(): array {
		$children = [];

		if ( false === $this->xml ) {
			return [];
		}

		$sitemap_children = count( $this->xml->sitemap );

		for ( $i = 0; $i < $sitemap_children; $i++ ) {
			$url = (string) $this->xml->sitemap[ $i ]->loc;
			if ( ! $url ) {
				continue;
			}
			$children [] = $url;
		}

		return $children;
	}
}
