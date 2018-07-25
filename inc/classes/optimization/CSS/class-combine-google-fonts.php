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
	 */
	public function __construct() {
		$this->fonts   = [];
		$this->subsets = [];
	}

	/**
	 * Combines multiple Google Fonts links into one
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function optimize( $html ) {
		$html_nocomments = preg_replace( '/<!--(.*)-->/Uis', '', $html );
		$fonts           = $this->find( '<link(?:\s+(?:(?!href\s*=\s*)[^>])+)?(?:\s+href\s*=\s*([\'"])((?:https?:)?\/\/fonts\.googleapis\.com\/css(?:(?!\1).)+)\1)(?:\s+[^>]*)?>', $html_nocomments );

		if ( ! $fonts ) {
			return $html;
		}

		$this->parse( $fonts );

		if ( empty( $this->fonts ) ) {
			return $html;
		}

		$html = str_replace( '</title>', '</title>' . $this->get_combine_tag(), $html );

		foreach ( $fonts as $font ) {
			$html = str_replace( $font[0], '', $html );
		}

		return $html;
	}

	/**
	 * Finds links to Google fonts
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $pattern Pattern to search for.
	 * @param string $html HTML content.
	 * @return bool|array
	 */
	protected function find( $pattern, $html ) {
		preg_match_all( '/' . $pattern . '/Umsi', $html, $matches, PREG_SET_ORDER );

		if ( count( $matches ) <= 1 ) {
			return false;
		}

		return $matches;
	}

	/**
	 * Parses found matches to extract fonts and subsets
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param Array $matches Found matches for the pattern.
	 * @return void
	 */
	protected function parse( $matches ) {
		foreach ( $matches as $match ) {
			$query = \rocket_extract_url_component( $match[2], PHP_URL_QUERY );

			if ( ! isset( $query ) ) {
				return;
			}

			$query = html_entity_decode( $query );
			$font  = wp_parse_args( $query );

			// Add font to the collection.
			$this->fonts[] = rawurlencode( htmlentities( $font['family'] ) );

			// Add subset to collection.
			$this->subsets[] = isset( $font['subset'] ) ? rawurlencode( htmlentities( $font['subset'] ) ) : '';
		}

		// Concatenate fonts tag.
		$this->subsets = ( $this->subsets ) ? '&subset=' . implode( ',', array_filter( array_unique( $this->subsets ) ) ) : '';
		$this->fonts   = implode( '|', array_filter( array_unique( $this->fonts ) ) );
		$this->fonts   = str_replace( '|', '%7C', $this->fonts );
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
