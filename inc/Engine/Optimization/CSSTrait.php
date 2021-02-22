<?php

namespace WP_Rocket\Engine\Optimization;

use WP_Rocket\Dependencies\PathConverter\ConverterInterface;
use WP_Rocket\Dependencies\PathConverter\Converter;

trait CSSTrait {
	/**
	 * Rewrites the paths inside the CSS file content
	 *
	 * @since 3.1
	 *
	 * @param string $source  Source filepath.
	 * @param string $target  Target filepath.
	 * @param string $content File content.
	 * @return string
	 */
	public function rewrite_paths( $source, $target, $content ) {
		/**
		 * Filters the source path for an asset inside a CSS file
		 *
		 * @since 3.3.1
		 *
		 * @param string $source Source filepath.
		 */
		$source = apply_filters( 'rocket_css_asset_source_path', $source );

		/**
		 * Filters the target path for an asset inside a CSS file
		 *
		 * @since 3.3.1
		 *
		 * @param string $target Target filepath.
		 */
		$target = apply_filters( 'rocket_css_asset_target_path', $target );

		/**
		 * Filters the content of a CSS file
		 *
		 * @since 3.4
		 *
		 * @param string $content CSS content.
		 * @param string $source  Source filepath.
		 * @param string $target  Target filepath.
		 */
		return apply_filters( 'rocket_css_content', $this->move( $this->get_converter( $source, $target ), $content, $source ), $source, $target );
	}

	/**
	 * Get an instance of the Converter class
	 *
	 * @param string $source Source filepath.
	 * @param string $target Destination filepath.
	 * @return Converter
	 */
	protected function get_converter( $source, $target ) {
		return new Converter( $source, $target );
	}

	/**
	 * Moving a css file should update all relative urls.
	 * Relative references (e.g. ../images/image.gif) in a certain css file,
	 * will have to be updated when a file is being saved at another location
	 * (e.g. ../../images/image.gif, if the new CSS file is 1 folder deeper).
	 *
	 * Method copied from WP_Rocket\Dependencies\Minify\CSS;
	 *
	 * @param ConverterInterface $converter Relative path converter.
	 * @param string             $content   The CSS content to update relative urls for.
	 * @param string             $source    The source path or URL for the CSS file.
	 *
	 * @return string
	 */
	protected function move( ConverterInterface $converter, $content, $source ) {
		/*
		 * Relative path references will usually be enclosed by url(). @import
		 * is an exception, where url() is not necessary around the path (but is
		 * allowed).
		 * This *could* be 1 regular expression, where both regular expressions
		 * in this array are on different sides of a |. But we're using named
		 * patterns in both regexes, the same name on both regexes. This is only
		 * possible with a (?J) modifier, but that only works after a fairly
		 * recent PCRE version. That's why I'm doing 2 separate regular
		 * expressions & combining the matches after executing of both.
		 */
		$relative_regexes = [
			// url(xxx).
			'/
			# open url()
			url\(

				\s*

				# open path enclosure
				(?P<quotes>["\'])?

					# fetch path
					(?P<path>.+?)

				# close path enclosure
				(?(quotes)(?P=quotes))

				\s*

			# close url()
			\)

			/ix',

			// @import "xxx"
			'/
			# import statement
			@import

			# whitespace
			\s+

				# we don\'t have to check for @import url(), because the
				# condition above will already catch these

				# open path enclosure
				(?P<quotes>["\'])

					# fetch path
					(?P<path>.+?)

				# close path enclosure
				(?P=quotes)

			/ix',
		];

		// find all relative urls in css.
		$matches = [];
		foreach ( $relative_regexes as $relative_regex ) {
			if ( preg_match_all( $relative_regex, $content, $regex_matches, PREG_SET_ORDER ) ) {
				$matches = array_merge( $matches, $regex_matches );
			}
		}

		$search  = [];
		$replace = [];

		// loop all urls.
		foreach ( $matches as $match ) {
			// determine if it's a url() or an @import match.
			$type = ( strpos( $match[0], '@import' ) === 0 ? 'import' : 'url' );

			$url = $match['path'];
			if ( ! preg_match( '/^(data:|https?:|\\/)/', $url ) ) {
				// attempting to interpret GET-params makes no sense, so let's discard them for awhile.
				$params = strrchr( $url, '?' );
				$url    = $params ? substr( $url, 0, -strlen( $params ) ) : $url;

				// fix relative url.
				$url = filter_var( $source, FILTER_VALIDATE_URL ) ? dirname( $source ) . '/' . ltrim( $url, '/' ) : $converter->convert( $url );

				// now that the path has been converted, re-apply GET-params.
				$url .= $params;
			}

			/*
			 * Urls with control characters above 0x7e should be quoted.
			 * According to Mozilla's parser, whitespace is only allowed at the
			 * end of unquoted urls.
			 * Urls with `)` (as could happen with data: uris) should also be
			 * quoted to avoid being confused for the url() closing parentheses.
			 * And urls with a # have also been reported to cause issues.
			 * Urls with quotes inside should also remain escaped.
			 *
			 * @see https://developer.mozilla.org/nl/docs/Web/CSS/url#The_url()_functional_notation
			 * @see https://hg.mozilla.org/mozilla-central/rev/14abca4e7378
			 * @see https://github.com/matthiasmullie/minify/issues/193
			 */
			$url = trim( $url );
			if ( preg_match( '/[\s\)\'"#\x{7f}-\x{9f}]/u', $url ) ) {
				$url = $match['quotes'] . $url . $match['quotes'];
			}

			// build replacement.
			$search[] = $match[0];
			if ( 'url' === $type ) {
				$replace[] = 'url(' . $url . ')';
			} elseif ( 'import' === $type ) {
				$replace[] = '@import "' . $url . '"';
			}
		}

		// replace urls.
		return str_replace( $search, $replace, $content );
	}

	/**
	 * Applies font-display:swap to all font-family rules without a previously set font-display property.
	 *
	 * @since 3.7
	 *
	 * @param string $css_file_content CSS file content to modify.
	 *
	 * @return string Modified CSS content.
	 */
	private function apply_font_display_swap( $css_file_content ) {
		$css_file_content = (string) $css_file_content;

		return preg_replace_callback(
			'/(?:@font-face)\s*{(?<value>[^}]+)}/i',
			function ( $matches ) {
				if ( preg_match( '/font-display:\s*(?<swap_value>\w*);?/i', $matches['value'], $attribute ) ) {
					return 'swap' === strtolower( $attribute['swap_value'] )
						? $matches[0]
						: str_replace( $attribute['swap_value'], 'swap', $matches[0] );
				} else {
					$swap = "font-display:swap;{$matches['value']}";
				}

				return str_replace( $matches['value'], $swap, $matches[0] );
			},
			$css_file_content
		);
	}
}
