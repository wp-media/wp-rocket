<?php

namespace WP_Rocket\Engine\Optimization;

use WP_Rocket\Dependencies\PathConverter\ConverterInterface;
use WP_Rocket\Dependencies\PathConverter\Converter;

trait CSSTrait {

	/**
	 * Currently imported files.
	 *
	 * @var array
	 */
	private $imports = [];

	/**
	 * Found charset on CSS.
	 *
	 * @var null|string
	 */
	private $found_charset = null;

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

		$content = $this->move( $this->get_converter( $source, $target ), $content, $source );

		$this->set_cached_import( $source );

		$content = $this->combine_imports( $content, $target );

		/**
		 * Filters the content of a CSS file
		 *
		 * @since 3.4
		 *
		 * @param string $content CSS content.
		 * @param string $source  Source filepath.
		 * @param string $target  Target filepath.
		 */
		return apply_filters( 'rocket_css_content', $content, $source, $target );
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
	 * Replace local imports with their contents recursively.
	 *
	 * @since 3.8.6
	 *
	 * @param string $content CSS Content.
	 * @param string $target Target CSS file path.
	 *
	 * @return string
	 */
	protected function combine_imports( $content, $target ) {
		$import_regexes = [
			// @import url(xxx)
			'/
		# import statement
		@import

		# whitespace
		\s+

			# open url()
			url\(

				# (optional) open path enclosure
				(?P<quotes>["\']?)

					# fetch path
					(?P<path>.+?)

				# (optional) close path enclosure
				(?P=quotes)

			# close url()
			\)

			# (optional) trailing whitespace
			\s*

			# (optional) media statement(s)
			(?P<media>[^;]*)

			# (optional) trailing whitespace
			\s*

		# (optional) closing semi-colon
		;?

		/ix',

			// @import 'xxx'
			'/

		# import statement
		@import

		# whitespace
		\s+

			# open path enclosure
			(?P<quotes>["\'])

				# fetch path
				(?P<path>.+?)

			# close path enclosure
			(?P=quotes)

			# (optional) trailing whitespace
			\s*

			# (optional) media statement(s)
			(?P<media>[^;]*)

			# (optional) trailing whitespace
			\s*

		# (optional) closing semi-colon
		;?

		/ix',
		];

		// find all relative imports in css.
		$matches = [];
		foreach ( $import_regexes as $import_regexe ) {
			if ( preg_match_all( $import_regexe, $content, $regex_matches, PREG_SET_ORDER ) ) {
				$matches = array_merge( $matches, $regex_matches );
			}
		}

		if ( empty( $matches ) ) {
			return $content;
		}

		$search  = [];
		$replace = [];

		// loop the matches.
		foreach ( $matches as $match ) {
			/**
			 * Filter Skip import replacement for one file.
			 *
			 * @since 3.8.6
			 *
			 * @param bool Skipped or not (Default not skipped).
			 * @param string $file_path Matched import path.
			 * @param string $import_match Full import match.
			 */
			if ( apply_filters( 'rocket_skip_import_replacement', false, $match['path'], $match ) ) {
				continue;
			}

			list( $import_path, $import_content ) = $this->get_internal_file_contents( $match['path'], dirname( $target ) );

			if ( empty( $import_content ) ) {
				continue;
			}

			if ( $this->check_cached_import( $import_path ) ) {
				$search[]  = $match[0];
				$replace[] = '';

				continue;
			}

			$this->set_cached_import( $import_path );

			// check if this is only valid for certain media.
			if ( ! empty( $match['media'] ) ) {
				$import_content = '@media ' . $match['media'] . '{' . $import_content . '}';
			}

			// Use recursion to rewrite paths and combine imports again for imported content.
			$import_content = $this->rewrite_paths( $import_path, $target, $import_content );

			// add to replacement array.
			$search[]  = $match[0];
			$replace[] = $import_content;
		}

		// replace the import statements.
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

	/**
	 * Get internal file full path and contents.
	 *
	 * @since 3.8.6
	 *
	 * @param string $file Internal file path (maybe external url or relative path).
	 * @param string $base_path Base path as reference for relative paths.
	 *
	 * @return array Array of two values ( full path, contents )
	 */
	private function get_internal_file_contents( $file, $base_path ) {
		if ( $this->is_external_path( $file ) && wp_http_validate_url( $file ) ) {
			return [ $file, false ];
		}

		// Remove query strings.
		$file = str_replace( '?' . wp_parse_url( $file, PHP_URL_QUERY ), '', $file );

		// Check if this file is readable or it's relative path so we add base_path at it's start.
		if ( ! rocket_direct_filesystem()->is_readable( $this->get_local_path( $file ) ) ) {
			$ds   = rocket_get_constant( 'DIRECTORY_SEPARATOR' );
			$file = $base_path . $ds . str_replace( '/', $ds, $file );
		}else {
			$file = $this->get_local_path( $file );
		}

		$file_type = wp_check_filetype( $file, [ 'css' => 'text/css' ] );

		if ( 'css' !== $file_type['ext'] ) {
			return [ $file, null ];
		}

		$import_content = rocket_direct_filesystem()->get_contents( $file );

		return [ $file, $import_content ];
	}

	/**
	 * Determines if the file is external.
	 *
	 * @since 3.8.6
	 *
	 * @param string $url URL of the file.
	 * @return bool True if external, false otherwise.
	 */
	protected function is_external_path( $url ) {
		$file = get_rocket_parse_url( $url );

		if ( empty( $file['path'] ) ) {
			return true;
		}

		$parsed_site_url = wp_parse_url( site_url() );

		if ( empty( $parsed_site_url['host'] ) ) {
			return true;
		}

		// This filter is documented in inc/Engine/Admin/Settings/Settings.php.
		$hosts   = (array) apply_filters( 'rocket_cdn_hosts', [], [ 'all' ] );
		$hosts[] = $parsed_site_url['host'];
		$langs   = get_rocket_i18n_uri();

		// Get host for all langs.
		foreach ( $langs as $lang ) {
			$url_host = wp_parse_url( $lang, PHP_URL_HOST );

			if ( ! isset( $url_host ) ) {
				continue;
			}

			$hosts[] = $url_host;
		}

		$hosts = array_unique( $hosts );

		if ( empty( $hosts ) ) {
			return true;
		}

		// URL has domain and domain is part of the internal domains.
		if ( ! empty( $file['host'] ) ) {
			foreach ( $hosts as $host ) {
				if ( false !== strpos( $url, $host ) ) {
					return false;
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Get local absolute path for image.
	 *
	 * @since 3.8.6
	 *
	 * @param string $url Image url.
	 *
	 * @return string Image absolute local path.
	 */
	private function get_local_path( $url ) {
		$url = $this->normalize_url( $url );

		$path = rocket_url_to_path( $url );
		if ( $path ) {
			return $path;
		}

		$relative_url = ltrim( wp_make_link_relative( $url ), '/' );
		$ds           = rocket_get_constant( 'DIRECTORY_SEPARATOR' );
		$base_path    = isset( $_SERVER['DOCUMENT_ROOT'] ) ? ( sanitize_text_field( wp_unslash( $_SERVER['DOCUMENT_ROOT'] ) ) . $ds ) : '';

		return $base_path . str_replace( '/', $ds, $relative_url );
	}

	/**
	 * Normalize relative url to full url.
	 *
	 * @since 3.8.6
	 *
	 * @param string $url Url to be normalized.
	 *
	 * @return string Normalized url.
	 */
	private function normalize_url( $url ) {
		$url_host = wp_parse_url( $url, PHP_URL_HOST );

		if ( ! empty( $url_host ) ) {
			return $url;
		}

		$relative_url        = ltrim( wp_make_link_relative( $url ), '/' );
		$site_url_components = wp_parse_url( site_url( '/' ) );

		return $site_url_components['scheme'] . '://' . $site_url_components['host'] . '/' . $relative_url;
	}

	/**
	 * Set cached import locally not to imported again.
	 *
	 * @param string $path Path to be cached.
	 */
	private function set_cached_import( string $path ) {
		$real_path                          = rocket_realpath( $path );
		$this->imports[ md5( $real_path ) ] = $real_path;
	}

	/**
	 * Check if path imported before.
	 *
	 * @param string $path Path to be checked.
	 *
	 * @return bool
	 */
	private function check_cached_import( string $path ) : bool {
		return isset( $this->imports[ md5( rocket_realpath( $path ) ) ] );
	}

	/**
	 * Move charset to top of CSS file OR remove all charsets for internal CSS.
	 *
	 * @param string $content CSS content.
	 * @param bool   $keep_first_charset Keep first charset if true, otherwise remove all charsets.
	 *
	 * @return string
	 */
	public function handle_charsets( string $content, bool $keep_first_charset = true ) : string {
		$new_content = preg_replace_callback( '/@charset\s+["|\'](.*?)["|\'];?/i', [ $this, 'match_charsets' ], $content );

		if ( ! $keep_first_charset ) {
			return $new_content;
		}

		if ( is_null( $this->found_charset ) ) {
			return $content;
		}

		return "@charset \"{$this->found_charset}\";" . $new_content;
	}

	/**
	 * Match each charset on the CSS file.
	 *
	 * @param array $match Match array.
	 *
	 * @return string
	 */
	private function match_charsets( array $match ) : string {
		if ( is_null( $this->found_charset ) ) {
			$this->found_charset = $match[1];
		}

		return '';
	}

}
