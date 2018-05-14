<?php
namespace WP_Rocket\Busting;

/**
 * Manages the cache busting of the Google Analytics file
 *
 * @since 3.1
 * @author Remy Perona
 */
class Google_Analytics extends Abstract_Busting {
	/**
	 * Google Analytics URL
	 *
	 * @var string;
	 */
	protected $url;

	/**
	 * {@inheritdoc}
	 */
	public function __construct( $busting_path, $busting_url ) {
		parent::__construct( $busting_path, $busting_url );

		$this->filename = 'ga-local';
		$this->url      = 'https://www.google-analytics.com/analytics.js';
	}

	/**
	 * {@inheritdoc}
	 */
	public function replace_url( $crawler ) {
		$node = $this->find( $crawler );

		if ( ! $node ) {
			return $crawler->saveHTML();
		}

		if ( ! $this->save( $this->url, $this->filename ) ) {
			return $crawler->saveHTML();
		}

		$this->replace( $node );

		return $crawler->saveHTML();
	}

	/**
	 * {@inheritdoc}
	 */
	protected function find( $crawler ) {
		try {
			$nodes = $crawler->filter( 'script:not([src])' );
		} catch ( Exception $e ) {
			return false;
		}

		if ( ! $nodes->count() ) {
			return false;
		}

		$matches = $nodes->each( function( \Wa72\HtmlPageDom\HtmlPageCrawler $node, $i ) {
			if ( false !== \strpos( $node->html(), 'GoogleAnalyticsObject' ) ) {
				return $node;
			}

			return false;
		} );

		if ( empty( $matches ) ) {
			return false;
		}

		return current( array_filter( array_unique( $matches ) ) );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function replace( $node ) {
			$node->html( str_replace( $this->url, $this->get_busting_url(), $node->html() ) );
			$this->is_replaced = true;
	}

	/**
	 * Returns if the replacement was sucessful or not
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return boolean
	 */
	public function is_replaced() {
		return $this->is_replaced;
	}
}
