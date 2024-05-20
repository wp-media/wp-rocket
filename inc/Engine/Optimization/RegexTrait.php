<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization;

trait RegexTrait {

	/**
	 * Array of replaced xmp tags
	 *
	 * @var array
	 */
	private $xmp_replace = [];

	/**
	 * Array of replaced html tags.
	 *
	 * @var array
	 */
	private $html_replace = [];

	/**
	 * Array of replaced svg tags
	 *
	 * @var array
	 */
	private $svg_replace = [];
	/**
	 * Finds nodes matching the pattern in the HTML.
	 *
	 * @param string $pattern Pattern to match.
	 * @param string $html HTML content.
	 * @param string $modifiers Regex modifiers.
	 * @param mixed  $flag Flag to use.
	 * @return array
	 */
	protected function find( string $pattern, string $html, string $modifiers = 'Umsi', $flag = PREG_SET_ORDER ) {
		preg_match_all( '/' . $pattern . '/' . $modifiers, $html, $matches, $flag );

		if ( empty( $matches ) ) {
			return [];
		}

		return $matches;
	}

	/**
	 * Hides unwanted blocks from the HTML to be parsed for optimization
	 *
	 * @since 3.1.4
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	protected function hide_comments( $html ) {
		$replace = preg_replace( '#<!--\s*noptimize\s*-->.*?<!--\s*/\s*noptimize\s*-->#is', '', $html );

		if ( null === $replace ) {
			return $html;
		}

		$replace = preg_replace( '/<!--(.*)-->/Uis', '', $replace );

		if ( null === $replace ) {
			return $html;
		}

		return $replace;
	}

	/**
	 * Hides scripts from the HTML to be parsed when removing CSS from it
	 *
	 * @since 3.10.2
	 *
	 * @param string $html HTML content.
	 *
	 * @return string
	 */
	protected function hide_scripts( $html ) {
		$replace = preg_replace( '#<script[^>]*>.*?<\/script\s*>#mis', '', $html );

		if ( null === $replace ) {
			return $html;
		}

		return $replace;
	}

	/**
	 * Hides <noscript> blocks from the HTML to be parsed.
	 *
	 * @param string $html HTML content.
	 *
	 * @return string
	 */
	protected function hide_noscripts( $html ) {
		$replace = preg_replace( '#<noscript[^>]*>.*?<\/noscript\s*>#mis', '', $html );

		if ( null === $replace ) {
			return $html;
		}

		return $replace;
	}


	/**
	 * Replace HTML comments.
	 *
	 * @param string $html HTML content.
	 *
	 * @return string
	 */
	protected function replace_html_comments( string $html ): string {
		$this->html_replace = [];

		$regex         = '#<!--.*-->#iUs';
		$replaced_html = preg_replace_callback( $regex, [ $this, 'replace_html_comment' ], $html );

		if ( empty( $replaced_html ) ) {
			return $html;
		}

		return $replaced_html;
	}

	/**
	 * Replace html with comment
	 *
	 * @param array $match HTML comment.
	 * @return string
	 */
	protected function replace_html_comment( $match ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.matchFound
		$key                        = sprintf( '<!-- %s -->', uniqid( 'WPR_HTML_COMMENT_' ) );
		$this->html_replace[ $key ] = $match[0];
		return $key;
	}

	/**
	 * Restore html with comment
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	protected function restore_html_comments( $html ) {
		if ( empty( $this->html_replace ) ) {
			return $html;
		}

		return str_replace( array_keys( $this->html_replace ), array_values( $this->html_replace ), $html );
	}

	/**
	 * Replace <xmp> tags in the HTML with comment
	 *
	 * @since 3.12.3
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	protected function replace_xmp_tags( $html ) {
		$this->xmp_replace = [];
		$regex             = '#<xmp.*>.*</xmp>#Uis';
		$replaced_html     = preg_replace_callback( $regex, [ $this, 'replace_xmp' ], $html );

		if ( empty( $replaced_html ) ) {
			return $html;
		}

		return $replaced_html;
	}

	/**
	 * Replace <svg> tags in the HTML with comment
	 *
	 * @since 3.12.5.3
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	protected function replace_svg_tags( $html ) {
		$this->svg_replace = [];
		$regex             = '#<\s*svg.*>.*<\s*\\\\?/\s*svg\s*>#Uis';
		$replaced_html     = preg_replace_callback( $regex, [ $this, 'replace_svg' ], $html );

		if ( empty( $replaced_html ) ) {
			return $html;
		}

		return $replaced_html;
	}

	/**
	 * Replace svg with comment
	 *
	 * @since 3.12.3
	 *
	 * @param array $match svg tag.
	 * @return string
	 */
	protected function replace_svg( $match ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.matchFound
		$key                       = sprintf( '<!-- %s -->', uniqid( 'WPR_SVG_' ) );
		$this->svg_replace[ $key ] = $match[0];
		return $key;
	}

	/**
	 * Replace xmp with comment
	 *
	 * @since 3.12.3
	 *
	 * @param array $match xmp tag.
	 * @return string
	 */
	protected function replace_xmp( $match ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.matchFound
		$key                       = sprintf( '<!-- %s -->', uniqid( 'WPR_XMP_' ) );
		$this->xmp_replace[ $key ] = $match[0];
		return $key;
	}

	/**
	 * Restore <svg> tags
	 *
	 * @since 3.12.5.3
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	protected function restore_svg_tags( $html ) {
		if ( empty( $this->svg_replace ) ) {
			return $html;
		}

		return str_replace( array_keys( $this->svg_replace ), array_values( $this->svg_replace ), $html );
	}

	/**
	 * Restore <xmp> tags
	 *
	 * @since 3.12.3
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	protected function restore_xmp_tags( $html ) {
		if ( empty( $this->xmp_replace ) ) {
			return $html;
		}

		return str_replace( array_keys( $this->xmp_replace ), array_values( $this->xmp_replace ), $html );
	}

	/**
	 * Checks if the page HTML is valid or not.
	 * Valid here means that it has a closing title tag.
	 *
	 * @param string $html Page HTML.
	 *
	 * @return bool
	 */
	private function html_has_title_tag( string $html ) {
		return (bool) preg_match( '#</title>#iU', $html );
	}
}
