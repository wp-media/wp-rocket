<?php
namespace WP_Rocket\Optimization\CSS;

use Wa72\HtmlPageDom\HtmlPageCrawler;

/**
 * Combine Google Fonts
 *
 * @since 3.1
 * @author Remy Perona
 */
class Combine_Google_Fonts {
	/**
	 * Crawler Instance
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @var HtmlPageCrawler
	 */
	protected $crawler;

	/**
	 * Found fonts
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @var array
	 */
	protected $fonts;

	/**
	 * Found subsets
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @var array
	 */
	protected $subsets;

	/**
	 * Constructor
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param HtmlPageCrawler $crawler Crawler instance.
	 */
	public function __construct( HtmlPageCrawler $crawler ) {
		$this->crawler = $crawler;
		$this->fonts   = [];
		$this->subsets = [];
	}

	/**
	 * Combines multiple Google Fonts links into one
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return string
	 */
	public function optimize() {
		$nodes = $this->find( 'link[href*="fonts.googleapis.com/css"]' );

		if ( ! $nodes ) {
			return $this->crawler->saveHTML();
		}

		$this->parse( $nodes );

		if ( empty( $this->fonts ) ) {
			return $this->crawler->saveHTML();
		}

		if ( ! $this->combine() ) {
			return $this->crawler->saveHTML();
		}

		$nodes->remove();

		return $this->crawler->saveHTML();
	}

	/**
	 * Finds links to Google fonts
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $pattern Pattern to search for.
	 * @return bool\HtmlPageCrawler
	 */
	protected function find( $pattern ) {
		try {
			$nodes = $this->crawler->filter( $pattern );
		} catch ( Exception $e ) {
			return false;
		}

		if ( $nodes->count() <= 1 ) {
			return false;
		}

		return $nodes;
	}

	/**
	 * Parses found nodes to extract fonts and subsets
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param HtmlageCrawler $nodes Found nodes for the pattern.
	 * @return void
	 */
	protected function parse( $nodes ) {
		$nodes->each( function( \Wa72\HtmlPageDom\HtmlPageCrawler $node, $i ) {
			$query = \rocket_extract_url_component( $node->attr( 'href' ), PHP_URL_QUERY );

			if ( ! isset( $query ) ) {
				return;
			}

			$query = html_entity_decode( $query );
			$font  = wp_parse_args( $query );

			// Add font to the collection.
			$this->fonts[] = rawurlencode( htmlentities( $font['family'] ) );

			// Add subset to collection.
			$this->subsets[] = isset( $font['subset'] ) ? rawurlencode( htmlentities( $font['subset'] ) ) : '';
		} );

		// Concatenate fonts tag.
		$this->subsets = ( $this->subsets ) ? '&subset=' . implode( ',', array_filter( array_unique( $this->subsets ) ) ) : '';
		$this->fonts   = implode( '|', array_filter( array_unique( $this->fonts ) ) );
		$this->fonts   = str_replace( '|', '%7C', $this->fonts );
	}

	/**
	 * Inserts the combined Google fonts link
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return bool
	 */
	protected function combine() {
		try {
			$this->crawler->filter( 'title' )->after( $this->get_combine_tag() );
		} catch ( Exception $e ) {
			return false;
		}

		return true;
	}

	/**
	 * Returns the combined Google fonts link tag
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return string
	 */
	protected function get_combine_tag() {
		return '<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=' . $this->fonts . $this->subsets . '" />';
	}
}
