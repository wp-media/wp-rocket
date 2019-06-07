<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Get CNAMES hosts
 *
 * @since 2.3
 *
 * @param  string $zones CNAMES zones.
 * @return array $hosts CNAMES hosts
 */
function get_rocket_cnames_host( $zones = array( 'all' ) ) {
	$hosts = array();

	$cnames = get_rocket_cdn_cnames( $zones );
	if ( $cnames ) {
		foreach ( $cnames as $cname ) {
			$cname   = rocket_add_url_protocol( $cname );
			$hosts[] = rocket_extract_url_component( $cname, PHP_URL_HOST );
		}
	}

	return $hosts;
}

/**
 * Get an URL with one of CNAMES added in options
 *
 * @since 2.1
 *
 * @param  string $url The URL to parse.
 * @param  array  $zone (default: array( 'all' )).
 * @return string $url The URL with one of CNAMES
 */
function get_rocket_cdn_url( $url, $zone = array( 'all' ) ) {
	$cnames             = get_rocket_cdn_cnames( $zone );
	$wp_content_dirname = ltrim( str_replace( home_url(), '', WP_CONTENT_URL ), '/' ) . '/';
	$home               = home_url();

	if ( ( defined( 'DONOTCDN' ) && DONOTCDN ) || (int) get_rocket_option( 'cdn' ) === 0 || empty( $cnames ) || is_rocket_post_excluded_option( 'cdn' ) ) {
		return $url;
	}

	$parse_url          = get_rocket_parse_url( $url );
	$parse_url['query'] = ! empty( $parse_url['query'] ) ? '?' . $parse_url['query'] : '';

	// Exclude rejected & external files from CDN.
	$rejected_files = get_rocket_cdn_reject_files();
	if ( ( ! empty( $rejected_files ) && preg_match( '#(' . $rejected_files . ')#', $parse_url['path'] ) ) || ( ! empty( $parse_url['scheme'] ) && rocket_extract_url_component( home_url(), PHP_URL_HOST ) !== $parse_url['host'] && ! in_array( $parse_url['host'], get_rocket_i18n_host(), true ) ) ) {
		return $url;
	}

	if ( empty( $parse_url['scheme'] ) ) {
		// Check if the URL is external.
		if ( strpos( $parse_url['path'], $home ) === false && ! preg_match( '#(' . $wp_content_dirname . '|wp-includes)#', $parse_url['path'] ) ) {
			return $url;
		} else {
			$parse_url['path'] = str_replace( $home, '', ltrim( $parse_url['path'], '//' ) );
		}
	}

	$url = untrailingslashit( $cnames[ ( abs( crc32( $parse_url['path'] ) ) % count( $cnames ) ) ] ) . '/' . ltrim( $parse_url['path'], '/' ) . $parse_url['query'];
	$url = rocket_add_url_protocol( $url );
	return $url;
}

/**
 * Wrapper of get_rocket_cdn_url() and print result
 *
 * @since 2.1
 *
 * @param string $url The URL to parse.
 * @param array  $zone (default: array( 'all' )).
 */
function rocket_cdn_url( $url, $zone = array( 'all' ) ) {
	echo get_rocket_cdn_url( $url, $zone );
}

/**
 * Apply CDN on CSS properties (background, background-image, @import, src:url (fonts))
 *
 * @since 2.6
 *
 * @param  string $buffer file content.
 * @return string modified file content
 */
function rocket_cdn_css_properties( $buffer ) {
	$zone   = array(
		'all',
		'images',
		'css_and_js',
		'css',
	);
	$cnames = get_rocket_cdn_cnames( $zone );

	/**
	 * Filters the application of the CDN on CSS properties
	 *
	 * @since 2.6
	 *
	 * @param bool true to apply CDN to properties, false otherwise
	 */
	$do_rocket_cdn_css_properties = apply_filters( 'do_rocket_cdn_css_properties', true );

	if ( ! get_rocket_option( 'cdn' ) || ! $cnames || ! $do_rocket_cdn_css_properties ) {
		return $buffer;
	}

	preg_match_all( '/url\((?![\'"]?data)([^\)]+)\)/i', $buffer, $matches );

	if ( is_array( $matches ) ) {
		$i = 0;
		foreach ( $matches[1] as $url ) {
			$url = trim( $url, " \t\n\r\0\x0B\"'" );
			/**
			 * Filters the URL of the CSS property
			 *
			 * @since 2.8
			 *
			 * @param string $url URL of the CSS property
			 */
			$url      = get_rocket_cdn_url( apply_filters( 'rocket_cdn_css_properties_url', $url ), $zone );
			$property = str_replace( $matches[1][ $i ], $url, $matches[0][ $i ] );
			$buffer   = str_replace( $matches[0][ $i ], $property, $buffer );

			$i++;
		}
	}

	return $buffer;
}

/**
 * Apply CDN on custom data attributes.
 *
 * @since 2.5.5
 *
 * @param   string $html Original Output.
 * @return  string $html Output that will be printed
 */
function rocket_add_cdn_on_custom_attr( $html ) {
	if ( preg_match( '/(data-lazy-src|data-lazyload|data-src|data-retina)=[\'"]?([^\'"\s>]+)[\'"]/i', $html, $matches ) ) {
		$html = str_replace( $matches[2], get_rocket_cdn_url( $matches[2], array( 'all', 'images' ) ), $html );
	}

	return $html;
}


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

	if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
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
		$wp_content_dirname = wp_parse_url( content_url(), PHP_URL_PATH );

		$custom_media_uploads_dirname = '';
		$uploads_info                 = wp_upload_dir();

		if ( ! empty( $uploads_info['baseurl'] ) ) {
			$custom_media_uploads_dirname = '|' . trailingslashit( wp_parse_url( $uploads_info['baseurl'], PHP_URL_PATH ) );
		}

		// Get all images of the content.
		preg_match_all( '#<img([^>]+?)src=([\'"\\\]*)([^\'"\s\\\>]+)([\'"\\\]*)([^>]*)>#i', $html, $images_match );

		foreach ( $images_match[3] as $k => $image_url ) {

			$parse_url = get_rocket_parse_url( $image_url );
			$path      = trim( $parse_url['path'] );
			$host      = $parse_url['host'];

			if ( empty( $path ) || ! preg_match( '#(' . $wp_content_dirname . $custom_media_uploads_dirname . '|wp-includes)#', $path ) ) {
				continue;
			}

			if ( isset( $cnames[ $host ] ) ) {
				continue;
			}

			// Image path is relative, apply the host to it.
			if ( empty( $host ) ) {
				$image_url = home_url( '/' ) . ltrim( $image_url, '/' );
				$host      = rocket_extract_url_component( $image_url, PHP_URL_HOST );
			}

			// Check if the link isn't external.
			if ( rocket_extract_url_component( home_url(), PHP_URL_HOST ) !== $host ) {
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
					'rocket_cdn_images_html',
					sprintf(
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

	$image_types = [
		'jpg',
		'jpeg',
		'jpe',
		'png',
		'gif',
		'webp',
		'bmp',
		'tiff',
	];

	$other_types = [
		'mp3',
		'ogg',
		'mp4',
		'm4v',
		'avi',
		'mov',
		'flv',
		'swf',
		'webm',
		'pdf',
		'doc',
		'docx',
		'txt',
		'zip',
		'tar',
		'bz2',
		'tgz',
		'rar',
	];

	$zones = array_filter( array_unique( get_rocket_option( 'cdn_zone', [] ) ) );

	if ( empty( $zones ) ) {
		return $html;
	}

	if ( ! in_array( 'all', $zones, true ) && ! in_array( 'images', $zones, true ) ) {
		return $html;
	}

	$cdn_zones  = [];
	$file_types = [];

	if ( in_array( 'images', $zones, true ) ) {
		$cdn_zones[] = 'images';
		$file_types  = array_merge( $file_types, $image_types );
	}

	if ( in_array( 'all', $zones, true ) ) {
		$cdn_zones[] = 'all';
		$file_types  = array_merge( $file_types, $image_types, $other_types );
	}

	$cnames = get_rocket_cdn_cnames( $cdn_zones );

	if ( empty( $cnames ) ) {
		return $html;
	}

	/**
	 * Filters the filetypes allowed for the CDN
	 *
	 * @since 2.9
	 * @author Remy Perona
	 *
	 * @param array $filetypes Array of file types.
	 */
	$file_types = apply_filters( 'rocket_cdn_custom_filetypes', $file_types );
	$file_types = implode( '|', $file_types );

	preg_match_all( '#<a[^>]+?href=[\'"]?([^"\'>]+\.(?:' . $file_types . '))[\'"]?[^>]*>#i', $html, $matches );

	if ( ! (bool) $matches ) {
		return $html;
	}

	foreach ( $matches[1] as $key => $url ) {
		$url  = trim( $url, " \t\n\r\0\x0B\"'" );
		$url  = get_rocket_cdn_url( $url, $cdn_zones );
		$src  = str_replace( $matches[1][ $key ], $url, $matches[0][ $key ] );
		$html = str_replace( $matches[0][ $key ], $src, $html );
	}

	return $html;
}

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
		$src = rocket_add_url_protocol( $src );
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

