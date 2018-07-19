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
	 * Flag to track the replacement
	 *
	 * @var bool
	 */
	protected $is_replaced;

	/**
	 * {@inheritdoc}
	 */
	public function __construct( $busting_path, $busting_url ) {
		$this->busting_path = $busting_path . 'google-tracking/';
		$this->busting_url  = $busting_url . 'google-tracking/';
		$this->is_replaced  = false;
		$this->filename     = 'ga-local.js';
		$this->url          = 'https://www.google-analytics.com/analytics.js';
	}

	/**
	 * {@inheritdoc}
	 */
	public function replace_url( $crawler ) {
		$node = $this->find( $crawler );

		if ( ! $node ) {
			return $crawler->saveHTML();
		}

		if ( ! $this->save( $this->url ) ) {
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
			$node->html( preg_replace( '/(?:https?:)?\/\/www.google-analytics.com\/analytics.js/i', $this->get_busting_url(), $node->html() ) );
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

	/**
	 * Deletes the GA busting file
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return bool
	 */
	public function delete() {
		$file = $this->busting_path . $this->filename;

		return \rocket_direct_filesystem()->delete( $file, false, 'f' );
	}

	/**
	 * Gets the Google Analytics URL
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return string
	 */
	public function get_url() {
		return $this->url;
	}
}
