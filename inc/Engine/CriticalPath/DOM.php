<?php

namespace WP_Rocket\Engine\CriticalPath;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\DOM\HTMLDocument;

class DOM {

	/**
	 * Instance of Critical CSS.
	 *
	 * @var Critical_CSS
	 */
	protected $critical_css;

	/**
	 * Instance of options.
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * Instance of the DOM.
	 *
	 * @var HTMLDocument
	 */
	protected $dom;

	/**
	 * Creates an instance of the CriticalPath DOM Handler.
	 *
	 * @param CriticalCSS  $critical_css Critical CSS instance.
	 * @param Options_Data $options WP Rocket options.
	 * @param HTMLDocument $dom
	 */
	public function __construct( CriticalCSS $critical_css, Options_Data $options, HTMLDocument $dom ) {
		$this->critical_css = $critical_css;
		$this->options      = $options;
		$this->dom          = $dom;
	}

	/**
	 * Modifies the given HTML for async CSS, i.e. defer loading of CSS files.
	 *
	 * @since  2.10
	 *
	 * @param string $html HTML code.
	 *
	 * @return string Updated HTML code
	 */
	public function modify_html_for_async_css( $html ) {
		if ( ! $this->maybe_async_css() ) {
			return $html;
		}

		/**
		 * Filters the pattern used to get all stylesheets in the HTML.
		 *
		 * @since  2.10
		 *
		 * @param string $css_pattern Regex pattern to get all stylesheets in the HTML.
		 */
		$css_pattern = apply_filters(
			'rocket_async_css_regex_pattern',
			'/(?=<link[^>]*\s(rel\s*=\s*[\'"]stylesheet["\']))<link(?=[^<>]*?(?:(?<type>type\s*=\s*[\'"][^<>"\']*[\'"])|>))(?=[^<>]*?(?:(?<onload>onload\s*=\s*(.*))|>))(?=[^<>]*?(?:(?<media>media\s*=\s*[\'"][^<>"\']*[\'"])|>))(?=[^<>]*?(?:href\s*=\s*[\'"](?<url>.*?)[\'"]|>))(?=[^<>]*?(?:(?<rel>rel\s*=\s*[\'"]>[^<>"\']*)[\'"]|>))(?:.*?<\/\1>|[^<>]*>)/ix'
		);

		// Get all css files with this regex.
		preg_match_all( $css_pattern, $html, $tags_match );

		if ( empty( $tags_match[0] ) ) {
			return $html;
		}

		return $this->async_css_tag_update( $tags_match, $html );
	}

	/**
	 * Alters onload tag to contain this.media switch + original onload value.
	 *
	 * @param string $tag             Full link tag match.
	 * @param string $onload_attr     Matched onload tag for the current tag.
	 * @param string $original_media  Original media value.
	 * @param string $original_onload Original onload value.
	 *
	 * @return string Modified tag with the onload attribute.
	 */
	private function replace_onload_attribute( $tag, $onload_attr, $original_media, &$original_onload ) {
		$onload_delimiter = substr( $onload_attr, 7, 1 );
		$onload_end       = strpos( $onload_attr, $onload_delimiter . ' ' );
		if ( ! $onload_end ) {
			$onload_end = strpos( $onload_attr, $onload_delimiter . '>' );
		}

		$original_onload = substr( $onload_attr, 8, $onload_end - 8 );
		$original_onload = preg_replace( '@(this.media\s*=\s*[\'"]' . $original_media . '[\'";];*)@ix', '', $original_onload );
		$onload          = substr( $onload_attr, 0, $onload_end + 1 );

		return str_replace( $onload, 'onload=""', $tag );
	}

	/**
	 * Replace media tag with 'print' on link.
	 *
	 * @param string $tag            Full link tag match.
	 * @param string $media_attr     Matched media tag for the current tag.
	 * @param string $original_media Original media value.
	 *
	 * @return string Modified tag with the print media attribute.
	 */
	private function replace_media_attribute( $tag, $media_attr, &$original_media ) {
		preg_match( '/media\s*=\s*[\'"](?<media>.*)[\'"]/ix', $media_attr, $media_match );
		$original_media = $media_match['media'];
		if ( 'print' === $original_media ) {
			$original_media = 'all';
		}

		return str_replace( $media_attr, 'media="print"', $tag );
	}

	/**
	 * Updates the HTML code by defering all CSS matches files.
	 *
	 * @param array  $tags_match All matched css files.
	 * @param string $buffer     HTML code.
	 *
	 * @return string             Modified HTML code with defer CSS.
	 */
	private function async_css_tag_update( $tags_match, $buffer ) {
		$excluded_css = array_flip( $this->critical_css->get_exclude_async_css() );
		$noscripts    = '';
		foreach ( $tags_match[0] as $i => $tag ) {
			// Strip query args.
			$path = wp_parse_url( $tags_match['url'][ $i ], PHP_URL_PATH );

			// Check if this file should be deferred.
			if ( isset( $excluded_css[ $path ] ) ) {
				continue;
			}

			$original_media  = 'all';
			$original_onload = '';
			$media_tag       = empty( $tags_match['media'][ $i ] ) ? ' media="print"' : '';
			$onload_tag      = empty( $tags_match['onload'][ $i ] ) ? ' onload=""' : '';
			$tag             = str_replace( $tags_match['type'][ $i ], ' as="style" ' . $tags_match['type'][ $i ] . $media_tag . $onload_tag, $tag );
			if ( ! empty( $tags_match['media'][ $i ] ) ) {
				$tag = $this->replace_media_attribute( $tag, $tags_match['media'][ $i ], $original_media );
			}

			if ( ! empty( $tags_match['onload'][ $i ] ) ) {
				$tag = $this->replace_onload_attribute( $tag, $tags_match['onload'][ $i ], $original_media, $original_onload );
			}
			$tag       = str_replace( 'onload=""', 'onload="this.media=\'' . $original_media . '\'' . ( ! empty( $original_onload ) ? ';' . $original_onload : '' ) . '"', $tag );
			$buffer    = str_replace( $tags_match[0][ $i ], $tag, $buffer );
			$noscripts .= '<noscript>' . $tags_match[0][ $i ] . '</noscript>';
		}

		return str_replace( '</body>', $noscripts . '</body>', $buffer );
	}

	/**
	 * Checks if we should apply deferring of CSS files.
	 *
	 * @return bool True if we should, false otherwise.
	 */
	private function maybe_async_css() {
		if ( rocket_get_constant( 'DONOTROCKETOPTIMIZE' ) || rocket_get_constant( 'DONOTASYNCCSS' ) ) {
			return false;
		}
		if ( ! $this->options->get( 'async_css' ) ) {
			return false;
		}
		if ( empty( $this->critical_css->get_current_page_critical_css() ) && empty( $this->options->get( 'critical_css', '' ) ) ) {
			return false;
		}

		return ! is_rocket_post_excluded_option( 'async_css' );
	}
}
