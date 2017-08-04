<?php
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

/**
 * Check if minify cache file exist and create it if not
 *
 * @since 2.10 Use wp_safe_remote_get() instead of curl
 * @since 2.1
 *
 * @param string $url 		 The minified URL with Minify Library.
 * @param string $pretty_url The minified URL cache file.
 * @return bool True if sucessfully saved the minify cache file, false otherwise
 */
function rocket_fetch_and_cache_minify( $url, $pretty_url ) {

	$pretty_path = str_replace( WP_ROCKET_MINIFY_CACHE_URL, WP_ROCKET_MINIFY_CACHE_PATH, $pretty_url );

	// If minify cache file is already exist, return to get a coffee.
	if ( file_exists( $pretty_path ) ) {
		return true;
	}

	/**
	 * Filters the minify URL
	 *
	 * @since 2.10
	 * @author Remy Perona
	 *
	 * @param string $url The minify URL.
	 */
	$url = apply_filters(  'rocket_minify_bypass_varnish', $url );

	/**
	 * Filters the request arguments
	 *
	 * @author Remy Perona
	 * @since 2.10
	 *
	 * @param array $args Array of argument for the request.
	 */
	$args = apply_filters( 'rocket_fetch_minify_args', array() );

	$minify_result = wp_safe_remote_get( $url, $args );

	if ( 200 !== wp_remote_retrieve_response_code( $minify_result ) ) {
		return false;
	}

	$content = wp_remote_retrieve_body( $minify_result );
	// Create cache folders of the request uri.
	$cache_path = WP_ROCKET_MINIFY_CACHE_PATH . get_current_blog_id() . '/';
	if ( ! is_dir( $cache_path ) ) {
		rocket_mkdir_p( $cache_path );
	}

	// Apply CDN on CSS properties.
	if ( strrpos( $pretty_path, '.css' ) ) {
		$content = rocket_cdn_css_properties( $content );
	}

	// Save cache file.
	if ( rocket_put_content( $pretty_path, $content ) ) {
		return true;
	}

	return false;
}

/**
 * Minify a file and return the URL
 *
 * @since 2.10
 *
 * @param string $file File to minify.
 * @param bool   $force_pretty_url (default: true).
 * @param string $pretty_filename (default: null) The new filename if $force_pretty_url set to true.
 * @return string URL of the minified file
 */
function get_rocket_minify_file( $file, $force_pretty_url = true, $pretty_filename = null ) {

	$base_url 	= WP_ROCKET_URL . 'min/?f=';

	$file = parse_url( $file, PHP_URL_PATH );
	$file = trim( $file );

	if ( empty( $file ) ) {
		return false;
	}

	// Replace "//" by "/" because it cause an issue with Minify Library!
	$file = str_replace( '//' , '/', $file );

	/**
	 * Filter file to add in minification process
	 *
	 * @since 2.4
	 *
	 * @param string $file The file path
	*/
	$file = apply_filters( 'rocket_pre_minify_path', $file );

	$url = $base_url . $file;
	$ext = pathinfo( $url, PATHINFO_EXTENSION );

	if ( $force_pretty_url && ( defined( 'SCRIPT_DEBUG' ) && ! SCRIPT_DEBUG ) ) {
		/**
		 * Filters the minify URL
		 *
		 * If true returns,
		 * the minify URL like example.com/wp-content/plugins/wp-rocket/min/?f=...
		 *
		 * @since 2.1
		 *
		 * @param bool
		*/
		if ( ! apply_filters( 'rocket_minify_debug', false ) ) {
			$blog_id = get_current_blog_id();
			$pretty_url = ! $pretty_filename ? WP_ROCKET_MINIFY_CACHE_URL . $blog_id . '/' . md5( $url . get_rocket_option( 'minify_' . $ext . '_key', create_rocket_uniqid() ) ) . '.' . $ext : WP_ROCKET_MINIFY_CACHE_URL . $blog_id . '/' . $pretty_filename . '.' . $ext;

			/**
			 * Filters the pretty minify URL
			 *
			 * @since 2.1
			 *
			 * @param string $pretty_url Pretty URL.
			 * @param string $pretty_filename Pretty filename.
			*/
			$pretty_url = apply_filters( 'rocket_minify_pretty_url', $pretty_url, $pretty_filename );

			$url = rocket_fetch_and_cache_minify( $url, $pretty_url ) ? $pretty_url : $url;
		}
	}

	// If CSS & JS use a CDN.
	$url = get_rocket_cdn_url( $url, array( 'all', 'css_and_js', $ext ) );

	if ( 'css' === $ext ) {
		/**
		 * Filters CSS file URL with CDN hostname
		 *
		 * @since 2.1
		 *
		 * @param string $url Minified file URL.
		*/
		$url = apply_filters( 'rocket_css_url', $url );

	} elseif ( 'js' === $ext ) {
		/**
		 * Filters JavaScript file URL with CDN hostname
		 *
		 * @since 2.1
		 *
		 * @param string $url Minified file URL.
		*/
		$url = apply_filters( 'rocket_js_url', $url );
	}

	return $url;
}

/**
 * Get tag of a group of files or JS minified CSS
 *
 * @since 2.1
 *
 * @param array  $files List of files to minify (CSS or JS).
 * @param bool   $force_pretty_url (default: true).
 * @param string $pretty_filename (default: null) The new filename if $force_pretty_url set to true.
 * @return string $tags HTML tags for the minified CSS/JS files
 */
function get_rocket_minify_files( $files, $force_pretty_url = true, $pretty_filename = null ) {
	/**
	 * Get the internal CSS Files
	 * To avoid conflicts with file URLs are too long for browsers,
	 * cut into several parts concatenated files
	 */
	$tags 		= '';
	$data_attr  = 'data-minify="1"';
	$urls 		= array( 0 => '' );
	$base_url 	= WP_ROCKET_URL . 'min/?f=';
	$files  	= is_array( $files ) ? $files : (array) $files;
	$files      = array_filter( $files );

	if ( ! (bool) $files ) {
		return $tags;
	}

	$i = 0;
	foreach ( $files as $file ) {
		$file = parse_url( $file, PHP_URL_PATH );
		$file = trim( $file );

		if ( empty( $file ) ) {
			continue;
		}

		// Replace "//" by "/" because it cause an issue with Minify Library!
		$file = str_replace( '//' , '/', $file );

		/**
		 * Filters the total number of files generated by the minification
		 *
		 * @since 2.1
		 *
		 * @param string $length 	The maximum number of characters in a URL.
		 * @param string $extension The file's extension.
		*/
		$filename_length = apply_filters( 'rocket_minify_filename_length', 255, pathinfo( $file, PATHINFO_EXTENSION ) );

		// +1 : we count the extra comma
		if ( strlen( $urls[ $i ] . $base_url . $file ) + 1 >= $filename_length ) {
			$i++;
			$urls[ $i ] = '';
		}

		/**
		 * Filter file to add in minification process
		 *
		 * @since 2.4
		 *
		 * @param string $file The file path
		*/
		$file = apply_filters( 'rocket_pre_minify_path', $file );

		$urls[ $i ] .= $file . ',';
	}

	foreach ( $urls as $url ) {
		$url = $base_url . rtrim( $url, ',' );
		$ext = pathinfo( $url, PATHINFO_EXTENSION );

		if ( $force_pretty_url && ( defined( 'SCRIPT_DEBUG' ) && ! SCRIPT_DEBUG ) ) {
			if ( ! apply_filters( 'rocket_minify_debug', false ) ) {
				$blog_id = get_current_blog_id();
				$pretty_url = ! $pretty_filename ? WP_ROCKET_MINIFY_CACHE_URL . $blog_id . '/' . md5( $url . get_rocket_option( 'minify_' . $ext . '_key', create_rocket_uniqid() ) ) . '.' . $ext : WP_ROCKET_MINIFY_CACHE_URL . $blog_id . '/' . $pretty_filename . '.' . $ext;

				/**
				 * Filters the pretty minify URL
				 *
				 * @since 2.1
				 *
				 * @param string $pretty_url Pretty URL.
				 * @param string $pretty_filename Pretty filename.
				*/
				$pretty_url = apply_filters( 'rocket_minify_pretty_url', $pretty_url, $pretty_filename );

				$url = rocket_fetch_and_cache_minify( $url, $pretty_url ) ? $pretty_url : $url;
			}
		}

		// If CSS & JS use a CDN.
		$url = get_rocket_cdn_url( $url, array( 'all', 'css_and_js', $ext ) );

		if ( 'css' === $ext ) {
			/**
			 * Filters CSS file URL with CDN hostname
			 *
			 * @since 2.1
			 *
			 * @param string $url Minified file URL.
			*/
			$url = apply_filters( 'rocket_css_url', $url );

			$tags .= sprintf( '<link rel="stylesheet" href="%s" %s/>', esc_attr( $url ), $data_attr );
		} elseif ( 'js' === $ext ) {
			/**
			 * Filters JavaScript file URL with CDN hostname
			 *
			 * @since 2.1
			 *
			 * @param string $url Minified file URL.
			*/
			$url = apply_filters( 'rocket_js_url', $url );

			$tags .= sprintf( '<script src="%s" %s></script>', esc_attr( $url ), $data_attr );
		}
	}

	return $tags;
}

/**
 * Wrapper of get_rocket_minify_files() and echoes the result
 *
 * @since 2.1
 *
 * @param array  $files List of files to minify (CSS or JS).
 * @param bool   $force_pretty_url (default: true).
 * @param string $pretty_filename The new filename if $force_pretty_url set to true (default: null).
 */
function rocket_minify_files( $files, $force_pretty_url = true, $pretty_filename = null ) {
	echo get_rocket_minify_files( $files, $force_pretty_url, $pretty_filename );
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
	$excluded_external_js = apply_filters( 'rocket_minify_excluded_external_js', array(
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
	) );

	return $excluded_external_js;
}
