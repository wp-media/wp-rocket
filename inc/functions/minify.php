<?php
use MatthiasMullie\Minify;

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Parses the buffer to minify the CSS and JS files
 *
 * @since 2.11
 * @since 2.1
 *
 * @param string $buffer    HTML output.
 * @param string $extension Type of files to minify.
 * @return string Updated HTML output.
 */
function rocket_minify_files( $buffer, $extension ) {
	global $wp_scripts, $rocket_js_enqueued_in_head;
	if ( 'css' === $extension ) {
		$concatenate = get_rocket_option( 'minify_concatenate_css', false ) ? true : false;
		// Get all css files with this regex.
		preg_match_all( apply_filters( 'rocket_minify_css_regex_pattern', '/<link\s*.+href=[\'|"]([^\'|"]+\.css?.+)[\'|"](.+)>/iU' ), $buffer, $tags_match, PREG_SET_ORDER );
	}

	if ( 'js' === $extension ) {
		$js_files_in_head = '';
		$concatenate      = get_rocket_option( 'minify_concatenate_js', false ) ? true : false;
		if ( $rocket_js_enqueued_in_head && is_array( $rocket_js_enqueued_in_head ) ) {
			$js_files_in_head = implode( '|', $rocket_js_enqueued_in_head );
		}

		// Get all js files with this regex.
		preg_match_all( apply_filters( 'rocket_minify_js_regex_pattern', '#<script[^>]+?src=[\'|"]([^\'|"]+\.js?.+)[\'|"].*>(?:<\/script>)#iU' ), $buffer, $tags_match, PREG_SET_ORDER );
	}

	$original_buffer   = $buffer;
	$files             = array();
	$excluded_files    = array();
	$external_js_files = array();

	foreach ( $tags_match as $tag ) {
		// Don't minify external files.
		if ( is_rocket_external_file( $tag[1], $extension ) ) {
			if ( 'js' === $extension && $concatenate ) {
				$host                 = rocket_extract_url_component( $tag[1], PHP_URL_HOST );
				$excluded_external_js = get_rocket_minify_excluded_external_js();
				if ( ! isset( $excluded_external_js[ $host ] ) ) {
					$external_js_files[] = $tag[0];
				}
			}
			continue;
		}

		// Don't minify excluded files.
		if ( is_rocket_minify_excluded_file( $tag, $extension ) ) {
			if ( $concatenate && 'js' === $extension && get_rocket_option( 'defer_all_js' ) && get_rocket_option( 'defer_all_js_safe' ) && false !== strpos( $tag[1], $wp_scripts->registered['jquery-core']->src ) ) {
				if ( get_rocket_option( 'remove_query_strings' ) ) {
					$external_js_files['jquery-cache-busting'] = str_replace( $tag[1], get_rocket_browser_cache_busting( $tag[1], 'script_loader_src' ), $tag[0] );
					$buffer                                    = str_replace( $tag[0], $external_js_files['jquery-cache-busting'], $buffer );
				} else {
					$external_js_files[] = $tag[0];
				}

				continue;
			}

			$excluded_files[] = $tag;
			continue;
		}

		if ( $concatenate ) {
			if ( 'js' === $extension ) {
				$file_path = rocket_clean_exclude_file( $tag[1] );

				if ( ! empty( $js_files_in_head ) && preg_match( '#(' . $js_files_in_head . ')#', $file_path ) ) {
					$files['header'][] = strtok( $tag[1], '?' );
				} else {
					$files['footer'][] = strtok( $tag[1], '?' );
				}
			} else {
				$files[] = strtok( $tag[1], '?' );
			}

			$buffer = str_replace( $tag[0], '', $buffer );
			continue;
		}

		// Don't minify if file is already minified.
		if ( preg_match( '/(?:-|\.)min.' . $extension . '/iU', $tag[1] ) ) {
			$excluded_files[] = $tag;
			continue;
		}

		// Don't minify jQuery included in WP core since it's already minified but without .min in the filename.
		if ( ! empty( $wp_scripts->registered['jquery-core']->src ) && false !== strpos( $tag[1], $wp_scripts->registered['jquery-core']->src ) ) {
			$excluded_files[] = $tag;
			continue;
		}

		$files[] = $tag;
	}

	if ( get_rocket_option( 'remove_query_strings' ) ) {
		foreach ( $excluded_files as $tag ) {
			if ( 'css' === $extension ) {
				$tag_cache_busting = str_replace( $tag[1], get_rocket_browser_cache_busting( $tag[1], 'style_loader_src' ), $tag[0] );
			}

			if ( 'js' === $extension ) {
				$tag_cache_busting = str_replace( $tag[1], get_rocket_browser_cache_busting( $tag[1], 'script_loader_src' ), $tag[0] );
			}

			$buffer = str_replace( $tag[0], $tag_cache_busting, $buffer );
		}
	}

	if ( empty( $files ) ) {
		return $buffer;
	}

	if ( ! $concatenate ) {
		foreach ( $files as $tag ) {
			$minify_url = get_rocket_minify_url( $tag[1], $extension );

			if ( ! $minify_url ) {
				continue;
			}

			$minify_tag = str_replace( $tag[1], $minify_url, $tag[0] );

			if ( 'css' === $extension ) {
				$minify_tag = str_replace( $tag[2], ' data-minify="1" ' . $tag[2], $minify_tag );
			}

			if ( 'js' === $extension ) {
				$minify_tag = str_replace( '></script>', ' data-minify="1"></script>', $minify_tag );
			}

			$buffer = str_replace( $tag[0], $minify_tag, $buffer );
		}

		return $buffer;
	}

	if ( 'js' === $extension ) {
		$minify_header_url = get_rocket_minify_url( $files['header'], $extension );
		$minify_url        = get_rocket_minify_url( $files['footer'], $extension );

		if ( ! $minify_header_url && ! $minify_url ) {
			return $original_buffer;
		}

		foreach ( $external_js_files as $external_js_file ) {
			$buffer = str_replace( $external_js_file, '', $buffer );
		}

		$minify_header_tag = '<script src="' . $minify_header_url . '" data-minify="1"></script>';
		$buffer            = preg_replace( '/<head(.*)>/U', '<head$1>' . implode( '', $external_js_files ) . $minify_header_tag, $buffer, 1 );

		$minify_tag = '<script src="' . $minify_url . '" data-minify="1"></script>';
		return str_replace( '</body>', $minify_tag . '</body>', $buffer );

	}

	if ( 'css' === $extension ) {
		$minify_url = get_rocket_minify_url( $files, $extension );

		if ( ! $minify_url ) {
			return $original_buffer;
		}

		$minify_tag = '<link rel="stylesheet" href="' . $minify_url . '" data-minify="1" />';
		return preg_replace( '/<head(.*)>/U', '<head$1>' . $minify_tag, $buffer, 1 );
	}
}

/**
 * Determines if the file is external
 *
 * @since 2.11
 * @author Remy Perona
 *
 * @param string $url       URL of the file.
 * @param string $extension File extension.
 * @return bool True if external, false otherwise
 */
function is_rocket_external_file( $url, $extension ) {
	$file       = get_rocket_parse_url( $url );
	$wp_content = get_rocket_parse_url( WP_CONTENT_URL );
	$hosts      = get_rocket_cnames_host( array( 'all', 'css_and_js', $extension ) );
	$hosts[]    = $wp_content['host'];
	$langs      = get_rocket_i18n_uri();

	// Get host for all langs.
	if ( $langs ) {
		foreach ( $langs as $lang ) {
			$hosts[] = rocket_extract_url_component( $lang, PHP_URL_HOST );
		}
	}

	$hosts_index = array_flip( array_unique( $hosts ) );

	// URL has domain and domain is not part of the internal domains.
	if ( isset( $file['host'] ) && ! empty( $file['host'] ) && ! isset( $hosts_index[ $file['host'] ] ) ) {
		return true;
	}

	// URL has no domain and doesn't contain the WP_CONTENT path or wp-includes.
	if ( ! isset( $file['host'] ) && ! preg_match( '#(' . $wp_content['path'] . '|wp-includes)#', $file['path'] ) ) {
		return true;
	}

	return false;
}

/**
 * Determines if it is a file excluded from minification
 *
 * @since 2.11
 * @author Remy Perona
 *
 * @param array  $tag       Array containing the matches from the regex.
 * @param string $extension File extension.
 * @return bool True if it is a file excluded, false otherwise
 */
function is_rocket_minify_excluded_file( $tag, $extension ) {
	// File should not be minified.
	if ( false !== strpos( $tag[0], 'data-minify=' ) || false !== strpos( $tag[0], 'data-no-minify=' ) ) {
		return true;
	}

	if ( 'css' === $extension ) {
		// CSS file media attribute is not all or screen.
		if ( false !== strpos( $tag[0], 'media=' ) && ! preg_match( '/media=["\'](?:["\']|[^"\']*?(all|screen)[^"\']*?["\'])/iU', $tag[0] ) ) {
			return true;
		}

		if ( false !== strpos( $tag[0], 'only screen and' ) ) {
			return true;
		}
	}

	$file_path = rocket_extract_url_component( $tag[1], PHP_URL_PATH );

	// File extension is not .css or .js.
	if ( pathinfo( $file_path, PATHINFO_EXTENSION ) !== $extension ) {
		return true;
	}

	$excluded_files = get_rocket_exclude_files( $extension );

	if ( ! empty( $excluded_files ) ) {
		foreach ( $excluded_files as $i => $excluded_file ) {
			$excluded_files[ $i ] = str_replace( '#', '\#', $excluded_file );
		}

		$excluded_files = implode( '|', $excluded_files );

		// File is excluded from minification/concatenation.
		if ( preg_match( '#^(' . $excluded_files . ')$#', $file_path ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Creates the minify URL if the minification is successful
 *
 * @since 2.11
 * @author Remy Perona
 *
 * @param string|array $files     Original file(s) URL(s).
 * @param string       $extension File(s) extension.
 * @return string|bool The minify URL if successful, false otherwise
 */
function get_rocket_minify_url( $files, $extension ) {
	if ( empty( $files ) ) {
		return false;
	}

	$hosts         = get_rocket_cnames_host( array( 'all', 'css_and_js', $extension ) );
	$hosts['home'] = rocket_extract_url_component( home_url(), PHP_URL_HOST );
	$hosts_index   = array_flip( $hosts );
	$minify_key    = get_rocket_option( 'minify_' . $extension . '_key', create_rocket_uniqid() );

	if ( is_string( $files ) ) {
		$file      = get_rocket_parse_url( $files );
		$file_path = rocket_realpath( strtok( $files, '?' ), true, $hosts_index );
		$unique_id = md5( $files . $minify_key );
		$filename  = preg_replace( '/\.(' . $extension . ')$/', '-' . $unique_id . '.' . $extension, ltrim( rocket_realpath( $file['path'], false, $hosts_index ), '/' ) );
	} else {
		foreach ( $files as $file ) {
			$file_path[] = rocket_realpath( $file, true, $hosts_index );
		}

		$files_hash = implode( ',', $files );
		$filename   = md5( $files_hash . $minify_key ) . '.' . $extension;
	}

	$minified_file = WP_ROCKET_MINIFY_CACHE_PATH . get_current_blog_id() . '/' . $filename;

	if ( ! file_exists( $minified_file ) ) {
		$minified_content = rocket_minify( $file_path, $extension );

		if ( ! $minified_content ) {
			return false;
		}

		$minify_filepath = rocket_write_minify_file( $minified_content, $minified_file );

		if ( ! $minify_filepath ) {
			return false;
		}
	}

	$minify_url = get_rocket_cdn_url( WP_ROCKET_MINIFY_CACHE_URL . get_current_blog_id() . '/' . $filename, array( 'all', 'css_and_js', $extension ) );

	if ( 'css' === $extension ) {
		/**
		 * Filters CSS file URL with CDN hostname
		 *
		 * @since 2.1
		 *
		 * @param string $minify_url Minified file URL.
		*/
		return apply_filters( 'rocket_css_url', $minify_url );
	}

	if ( 'js' === $extension ) {
		/**
		 * Filters JavaScript file URL with CDN hostname
		 *
		 * @since 2.1
		 *
		 * @param string $minify_url Minified file URL.
		*/
		return apply_filters( 'rocket_js_url', $minify_url );

	}
}

/**
 * Minifies the content
 *
 * @since 2.11
 * @author Remy Perona
 *
 * @param string|array $files     File(s) to minify.
 * @param string       $extension File(s) extension.
 * @return string|bool Minified content, false if empty
 */
function rocket_minify( $files, $extension ) {
	if ( 'css' === $extension ) {
		$minify = new Minify\CSS();
	} elseif ( 'js' === $extension ) {
		$minify = new Minify\JS();
	}

	$files = (array) $files;

	foreach ( $files as $file ) {
		$file_content = rocket_direct_filesystem()->get_contents( $file );
		if ( 'css' === $extension ) {
			$file_content = rocket_cdn_css_properties( Minify_CSS_UriRewriter::rewrite( $file_content, dirname( $file ) ) );
		}

		$minify->add( $file_content );
	}

	$minified_content = $minify->minify();

	if ( empty( $minified_content ) ) {
		return false;
	}

	return $minified_content;
}

/**
 * Writes the minified content to a file
 *
 * @since 2.11
 * @author Remy Perona
 *
 * @param string $content       Minified content.
 * @param string $minified_file Path to the minified file to write in.
 * @return bool True if successful, false otherwise
 */
function rocket_write_minify_file( $content, $minified_file ) {
	if ( file_exists( $minified_file ) ) {
		return true;
	}

	if ( ! rocket_mkdir_p( dirname( $minified_file ) ) ) {
		return false;
	}

	return rocket_put_content( $minified_file, $content );
}

/**
 * Concatenates Google Fonts tags (http://fonts.googleapis.com/css?...)
 *
 * @since 2.3
 *
 * @param string $buffer HTML content.
 * @return string Modified HTML content
 */
function rocket_concatenate_google_fonts( $buffer ) {
	// Get all Google Fonts CSS files.
	$buffer_without_comments = preg_replace( '/<!--(.*)-->/Uis', '', $buffer );
	preg_match_all( '/<link(?:\s+(?:(?!href\s*=\s*)[^>])+)?(?:\s+href\s*=\s*([\'"])((?:https?:)?\/\/fonts\.googleapis\.com\/css(?:(?!\1).)+)\1)(?:\s+[^>]*)?>/iU', $buffer_without_comments, $matches );

	if ( ! $matches[2] || 1 === count( $matches ) ) {
		return $buffer;
	}

	$fonts   = array();
	$subsets = array();

	foreach ( $matches[2] as $k => $font ) {
		// Get fonts name.
		$font = str_replace( array( '%7C', '%7c' ), '|', $font );
		$font = explode( 'family=', $font );
		$font = ( isset( $font[1] ) ) ? explode( '&', $font[1] ) : array();

		// Add font to the collection.
		$fonts = array_merge( $fonts, explode( '|', reset( $font ) ) );

		// Add subset to collection.
		$subset = ( is_array( $font ) ) ? end( $font ) : '';
		if ( false !== strpos( $subset, 'subset=' ) ) {
			$subset  = explode( 'subset=', $subset );
			$subsets = array_merge( $subsets, explode( ',', $subset[1] ) );
		}

		// Delete the Google Fonts tag.
		$buffer = str_replace( $matches[0][ $k ], '', $buffer );
	}

	// Concatenate fonts tag.
	$subsets = ( $subsets ) ? '&subset=' . implode( ',', array_filter( array_unique( $subsets ) ) ) : '';
	$fonts   = implode( '|', array_filter( array_unique( $fonts ) ) );
	$fonts   = str_replace( '|', '%7C', $fonts );

	if ( ! empty( $fonts ) ) {
		$fonts  = '<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=' . $fonts . $subsets . '" />';
		$buffer = preg_replace( '/<head(.*)>/U', '<head$1>' . $fonts, $buffer, 1 );
	}

	return $buffer;
}

/**
 * Minifies inline CSS
 *
 * @since 1.1.6
 *
 * @param string $css HTML content.
 * @return string Updated HTML content
 */
function rocket_minify_inline_css( $css ) {
	$minify = new Minify\CSS( $css );
	return $minify->minify();
}

/**
 * Minifies inline JavaScript
 *
 * @since 1.1.6
 *
 * @param string $js HTML content.
 * @return string Updated HTML content
 */
function rocket_minify_inline_js( $js ) {
	$minify = new Minify\JS( $js );
	return $minify->minify();
}

/**
 * Extracts IE conditionals tags and replace them with placeholders
 *
 * @since 1.0
 *
 * @param string $buffer HTML content.
 * @return string Updated HTML content
 */
function rocket_extract_ie_conditionals( $buffer ) {
	preg_match_all( '/<!--\[if[^\]]*?\]>.*?<!\[endif\]-->/is', $buffer, $conditionals_match );
	$buffer = preg_replace( '/<!--\[if[^\]]*?\]>.*?<!\[endif\]-->/is', '{{WP_ROCKET_CONDITIONAL}}', $buffer );

	$conditionals = array();
	foreach ( $conditionals_match[0] as $conditional ) {
		$conditionals[] = $conditional;
	}

	return array( $buffer, $conditionals );
}

/**
 * Replaces WP Rocket placeholders with IE condtional tags
 *
 * @since 1.0
 *
 * @param string $buffer HTML content.
 * @param array  $conditionals An array of HTML conditional tags.
 * @return string Updated HTML content
 */
function rocket_inject_ie_conditionals( $buffer, $conditionals ) {
	foreach ( $conditionals as $conditional ) {
		if ( false !== strpos( $buffer, '{{WP_ROCKET_CONDITIONAL}}' ) ) {
			$buffer = preg_replace( '/{{WP_ROCKET_CONDITIONAL}}/', $conditional, $buffer, 1 );
		} else {
			break;
		}
	}
	return $buffer;
}

/**
 * Get all JS externals files to exclude of the minification process
 *
 * @since 2.6
 *
 * @return array Array of excluded external JS
 */
function get_rocket_minify_excluded_external_js() {
	/**
	 * Filters JS externals files to exclude from the minification process (do not move into the header)
	 *
	 * @since 2.2
	 *
	 * @param array $hostnames Hostname of JS files to exclude.
	 */
	$excluded_external_js = apply_filters(
		'rocket_minify_excluded_external_js', array(
			'forms.aweber.com',
			'video.unrulymedia.com',
			'gist.github.com',
			'stats.wp.com',
			'stats.wordpress.com',
			'www.statcounter.com',
			'widget.rafflecopter.com',
			'widget-prime.rafflecopter.com',
			'widget.supercounters.com',
			'releases.flowplayer.org',
			'tools.meetaffiliate.com',
			'c.ad6media.fr',
			'cdn.stickyadstv.com',
			'www.smava.de',
			'contextual.media.net',
			'app.getresponse.com',
			'ap.lijit.com',
			'adserver.reklamstore.com',
			's0.wp.com',
			'wprp.zemanta.com',
			'files.bannersnack.com',
			'smarticon.geotrust.com',
			'js.gleam.io',
			'script.ioam.de',
			'ir-na.amazon-adsystem.com',
			'web.ventunotech.com',
			'verify.authorize.net',
			'ads.themoneytizer.com',
			'embed.finanzcheck.de',
			'imagesrv.adition.com',
			'js.juicyads.com',
			'form.jotformeu.com',
			'speakerdeck.com',
			'content.jwplatform.com',
			'ads.investingchannel.com',
			'app.ecwid.com',
			'www.industriejobs.de',
			's.gravatar.com',
			'cdn.jsdelivr.net',
			'cdnjs.cloudflare.com',
			'code.jquery.com',
		)
	);

	return array_flip( $excluded_external_js );
}
