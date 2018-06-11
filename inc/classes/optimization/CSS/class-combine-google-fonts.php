<?php
namespace WP_Rocket\Optimization\CSS;

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
	public function __construct( $crawler ) {
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
			// Get fonts name.
			$font = str_replace( array( '%7C', '%7c' ), '|', $node->attr( 'href' ) );
			$font = explode( 'family=', $font );
			$font = isset( $font[1] ) ? explode( '&', $font[1] ) : array();

			// Add font to the collection.
			$this->fonts = array_merge( $this->fonts, explode( '|', reset( $font ) ) );

			// Add subset to collection.
			$subset = ( is_array( $font ) ) ? end( $font ) : '';

			if ( false !== strpos( $subset, 'subset=' ) ) {
				$subset        = explode( 'subset=', $subset );
				$this->subsets = array_merge( $this->subsets, explode( ',', $subset[1] ) );
			}
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
			$this->crawler->filter( 'head' )->prepend( $this->get_combine_tag() );
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
