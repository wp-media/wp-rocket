<?php
namespace WP_Rocket\Optimization\CSS;

use MatthiasMullie\PathConverter\ConverterInterface;
use MatthiasMullie\PathConverter\Converter;
/**
 * Trait used to rewriter path of files inside CSS file contente
 *
 * @since 3.1
 * @author Remy Perona
 */
trait Path_Rewriter {
	/**
	 * Rewrites the paths inside the CSS file content
	 *
	 * @since 3.1
	 * @author Remy Perona
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
		 * @author Remy Perona
		 *
		 * @param string $source Source filepath.
		 */
		$source = apply_filters( 'rocket_css_asset_source_path', $source );

		/**
		 * Filters the target path for an asset inside a CSS file
		 *
		 * @since 3.3.1
		 * @author Remy Perona
		 *
		 * @param string $target Target filepath.
		 */
		$target = apply_filters( 'rocket_css_asset_target_path', $target );

		return \rocket_cdn_css_properties( $this->move( $this->get_converter( $source, $target ), $content ) );
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
	 * Method copied from MatthiasMullie\Minify\CSS;
	 *
	 * @param ConverterInterface $converter Relative path converter.
	 * @param string             $content   The CSS content to update relative urls for.
	 *
	 * @return string
	 */
	protected function move( ConverterInterface $converter, $content ) {
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
		$relativeRegexes = array(
			// url(xxx)
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
		);

		// find all relative urls in css.
		$matches = [];
		foreach ( $relativeRegexes as $relativeRegex ) {
			if ( preg_match_all( $relativeRegex, $content, $regexMatches, PREG_SET_ORDER ) ) {
				$matches = array_merge( $matches, $regexMatches );
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
				$url = $converter->convert( $url );

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
}
