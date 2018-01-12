<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Replace URL by CDN of all thumbnails and smilies.
 *
 * @since 2.1
 *
 * @param string $url URL of the file to replace the domain with the CDN.
 * @return string modified URL
 */
function rocket_cdn_file( $url ) {
	if ( defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) {
		return $url;
	}

	$ext = pathinfo( $url, PATHINFO_EXTENSION );

	if ( is_admin() || 'php' === $ext ) {
		return $url;
	}

	$filter = current_filter();

	$rejected_files = get_rocket_cdn_reject_files();
	if ( 'template_directory_uri' === $filter && ! empty( $rejected_files ) ) {
		return $url;
	}

	switch ( $filter ) {
		case 'wp_get_attachment_url':
		case 'wp_calculate_image_srcset':
			$zone = array( 'all', 'images' );
			break;
		case 'smilies_src':
			$zone = array( 'all', 'images' );
			break;
		case 'stylesheet_uri':
		case 'wp_minify_css_url':
		case 'wp_minify_js_url':
		case 'bwp_get_minify_src':
			$zone = array( 'all', 'css_and_js', $ext );
			break;
		default:
			$zone = array( 'all', $ext );
			break;
	}

	$cnames = get_rocket_cdn_cnames( $zone );
	if ( $cnames ) {
		$url = get_rocket_cdn_url( $url, $zone );
	}

	return $url;
}
add_filter( 'template_directory_uri'    , 'rocket_cdn_file', PHP_INT_MAX );
add_filter( 'wp_get_attachment_url'     , 'rocket_cdn_file', PHP_INT_MAX );
add_filter( 'smilies_src'               , 'rocket_cdn_file', PHP_INT_MAX );
add_filter( 'stylesheet_uri'            , 'rocket_cdn_file', PHP_INT_MAX );
// If for some completely unknown reason the user is using WP Minify or Better WordPress Minify instead of the WP Rocket minification.
add_filter( 'wp_minify_css_url'         , 'rocket_cdn_file', PHP_INT_MAX );
add_filter( 'wp_minify_js_url'          , 'rocket_cdn_file', PHP_INT_MAX );
add_filter( 'bwp_get_minify_src'        , 'rocket_cdn_file', PHP_INT_MAX );


/**
 * Replace URL by CDN of images displayed using wp_get_attachment_image_src
 *
 * @since 2.9.2
 * @author Remy Perona
 * @source https://github.com/wp-media/wp-rocket/issues/271#issuecomment-269849927
 *
 * @param array $image An array containing the src, width and height of the image.
 * @return array Array with updated src URL
 */
function rocket_cdn_attachment_image_src( $image ) {
	if ( defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) {
		return $image;
	}

	if ( ! (bool) $image ) {
		return $image;
	}

	if ( is_admin() || is_preview() || is_feed() ) {
		return $image;
	}

	$zones = array( 'all', 'images' );

	if ( ! (bool) get_rocket_cdn_cnames( $zones ) ) {
		return $image;
	}

	$image[0] = get_rocket_cdn_url( $image[0], $zones );

	return $image;
}
add_filter( 'wp_get_attachment_image_src', 'rocket_cdn_attachment_image_src', PHP_INT_MAX );

/**
 * Replace srcset URLs by CDN URLs for WP responsive images
 *
 * @since WP 4.4
 * @since 2.6.14
 * @author Remy Perona
 *
 * @param  array $sources multidimensional array containing srcset images urls
 * @return array $sources
 */
if ( function_exists( 'wp_calculate_image_srcset' ) ) :
	/**
	 * Replace srcset URLs by CDN URLs for WP responsive images
	 *
	 * @since WP 4.4
	 * @since 2.6.14
	 * @author Remy Perona
	 *
	 * @param  array $sources multidimensional array containing srcset images urls.
	 * @return array $sources
	 */
	function rocket_add_cdn_on_srcset( $sources ) {
		if ( defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) {
			return $sources;
		}

		if ( (bool) $sources ) {
			foreach ( $sources as $width => $data ) {
				$sources[ $width ]['url'] = rocket_cdn_file( $data['url'] );
			}
		}
		return $sources;
	}
	add_filter( 'wp_calculate_image_srcset', 'rocket_add_cdn_on_srcset', PHP_INT_MAX );
endif;

/**
 * Replace URL by CDN of all images display in a post content or a widget text.
 *
 * @since 2.1
 *
 * @param  string $html HTML content to parse.
 * @return string modified HTML content
 */
function rocket_cdn_images( $html ) {
	// Don't use CDN if the image is in admin, a feed or in a post preview.
	if ( is_admin() || is_feed() || is_preview() || empty( $html ) || defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) {
		return $html;
	}

	$zone   = array( 'all', 'images' );
	$cnames = get_rocket_cdn_cnames( $zone );

	if ( $cnames ) {

		$cnames             = array_flip( $cnames );
		$home_url           = home_url( '/' );
		$wp_content_dirname = str_replace( $home_url, '', WP_CONTENT_URL );
		// Get all images of the content.
		preg_match_all( '#<img([^>]+?)src=([\'"\\\]*)([^\'"\s\\\>]+)([\'"\\\]*)([^>]*)>#i', $html, $images_match );

		foreach ( $images_match[3] as $k => $image_url ) {

			$parse_url = get_rocket_parse_url( $image_url );
			$path      = trim( $parse_url['path'] );
			$host      = $parse_url['host'];

			if ( empty( $path ) || ! preg_match( '#(' . $wp_content_dirname . '|wp-includes)#', $path ) ) {
				continue;
			}

			if ( isset( $cnames[ $host ] ) ) {
				continue;
			}

			// Image path is relative, apply the host to it.
			if ( empty( $host ) ) {
				$image_url = $home_url . ltrim( $image_url, '/' );
				$host = rocket_extract_url_component( $image_url, PHP_URL_HOST );
			}

			// Check if the link isn't external.
			if ( rocket_extract_url_component( $home_url, PHP_URL_HOST ) !== $host ) {
				continue;
			}

			// Check if the URL isn't a DATA-URI.
			if ( false !== strpos( $image_url, 'data:image' ) ) {
				continue;
			}

			$html = str_replace(
				$images_match[0][ $k ],
				/**
				 * Filter the image HTML output with the CDN link
				 *
				 * @since 2.5.5
				 *
				 * @param array $html Output that will be printed.
				 */
				apply_filters(
					'rocket_cdn_images_html', sprintf(
						'<img %1$s %2$s %3$s>',
						trim( $images_match[1][ $k ] ),
						'src=' . $images_match[2][ $k ] . get_rocket_cdn_url( $image_url, $zone ) . $images_match[4][ $k ],
						trim( $images_match[5][ $k ] )
					)
				),
				$html
			);
		}
	}

	return $html;
}
add_filter( 'the_content', 'rocket_cdn_images', PHP_INT_MAX );
add_filter( 'widget_text', 'rocket_cdn_images', PHP_INT_MAX );
add_filter( 'rocket_buffer', 'rocket_cdn_images', PHP_INT_MAX );

/**
 * Replace URL by CDN of all inline styles containing url()
 *
 * @since 2.9
 * @author Remy Perona
 *
 * @param  string $html HTML content of the page.
 * @return string modified HTML content
 */
function rocket_cdn_inline_styles( $html ) {
	if ( is_preview() || empty( $html ) || defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) {
		return $html;
	}

	$zone = array(
		'all',
		'images',
		'css_and_js',
		'css',
	);

	$cnames = get_rocket_cdn_cnames( $zone );
	if ( $cnames ) {
		preg_match_all( '/url\((?![\'\"]?data)[\"\']?([^\)\"\']+)[\"\']?\)/i', $html, $matches );

		if ( (bool) $matches ) {
			foreach ( $matches[1] as $k => $url ) {
				$url = str_replace( array( ' ', '\t', '\n', '\r', '\0', '\x0B', '"', "'", '&quot;', '&#039;' ), '', $url );

				if ( '#' === substr( $url, 0, 1 ) ) {
					continue;
				}

				$url      = get_rocket_cdn_url( $url, $zone );
				$property = str_replace( $matches[1][ $k ], $url, $matches[0][ $k ] );
				$html     = str_replace( $matches[0][ $k ], $property, $html );
			}
		}
	}

	return $html;
}
add_filter( 'rocket_buffer', 'rocket_cdn_inline_styles', PHP_INT_MAX );

/**
 * Replace URL by CDN for custom files
 *
 * @since 2.9
 * @author Remy Perona
 *
 * @param string $html HTML content of the page.
 * @return string modified HTML content
 */
function rocket_cdn_custom_files( $html ) {
	if ( is_preview() || empty( $html ) || defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) {
		return $html;
	}

	$zone   = array(
		'all',
	);
	$cnames = get_rocket_cdn_cnames( $zone );

	if ( $cnames ) {

		/**
		 * Filters the filetypes allowed for the CDN
		 *
		 * @since 2.9
		 * @author Remy Perona
		 *
		 * @param array $filetypes Array of file types.
		 */
		$filetypes = apply_filters( 'rocket_cdn_custom_filetypes', array( 'mp3', 'ogg', 'mp4', 'm4v', 'avi', 'mov', 'flv', 'swf', 'webm', 'pdf', 'doc', 'docx', 'txt', 'zip', 'tar', 'bz2', 'tgz', 'rar', 'jpg', 'jpeg', 'jpe', 'png', 'gif', 'webp', 'bmp', 'tiff' ) );
		$filetypes = implode( '|', $filetypes );

		preg_match_all( '#<a[^>]+?href=[\'"]?([^"\'>]+\.(?:' . $filetypes . '))[\'"]?[^>]*>#i', $html, $matches );

		if ( (bool) $matches ) {
			$i = 0;
			foreach ( $matches[1] as $url ) {
				$url  = trim( $url, " \t\n\r\0\x0B\"'" );
				$url  = get_rocket_cdn_url( $url, $zone );
				$src  = str_replace( $matches[1][ $i ], $url, $matches[0][ $i ] );
				$html = str_replace( $matches[0][ $i ], $src, $html );
				$i++;
			}
		}
	}

	return $html;
}
add_filter( 'rocket_buffer', 'rocket_cdn_custom_files', 12 );

/**
 * Replace URL by CDN of all scripts and styles enqueues with WordPress functions
 *
 * @since 2.9 Only add protocol if $src is an absolute url
 * @since 2.1
 *
 * @param  string $src URL of the file.
 * @return string modified URL
 */
function rocket_cdn_enqueue( $src ) {
	// Don't use CDN if in admin, in login page, in register page or in a post preview.
	if ( is_admin() || is_preview() || in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ), true ) || defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) {
		return $src;
	}

	if ( rocket_extract_url_component( $src, PHP_URL_HOST ) !== '' ) {
		$src  = rocket_add_url_protocol( $src );
	}

	$zone = array( 'all', 'css_and_js' );

	// Add only CSS zone.
	if ( 'style_loader_src' === current_filter() ) {
		$zone[] = 'css';
	}

	// Add only JS zone.
	if ( 'script_loader_src' === current_filter() ) {
		$zone[] = 'js';
	}

	$cnames = get_rocket_cdn_cnames( $zone );
	if ( $cnames ) {
		// Check if the path isn't empty.
		if ( trim( rocket_extract_url_component( $src, PHP_URL_PATH ), '/' ) !== '' ) {
			$src = get_rocket_cdn_url( $src, $zone );
		}
	}

	return $src;
}
add_filter( 'style_loader_src', 'rocket_cdn_enqueue', PHP_INT_MAX - 1 );
add_filter( 'script_loader_src', 'rocket_cdn_enqueue', PHP_INT_MAX - 1 );
