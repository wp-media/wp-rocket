<?php

defined( 'ABSPATH' ) || exit;

/**
 * Get Zones linked to a Cloudflare account
 *
 * @since 2.9
 * @deprecated 3.4.1.2
 * @author Remy Perona
 *
 * @return Array List of zones or default no domain
 */
function get_rocket_cloudflare_zones() {
	_deprecated_function( __FUNCTION__ . '()', '3.4.1.2' );
	$cf_api_instance = get_rocket_cloudflare_api_instance();
	$domains         = array(
		'' => __( 'Choose a domain from the list', 'rocket' ),
	);

	if ( is_wp_error( $cf_api_instance ) ) {
		return $domains;
	}

	try {
		$cf_zone_instance = new Cloudflare\Zone( $cf_api_instance );
		$cf_zones         = $cf_zone_instance->zones( null, 'active', null, 50 );
		$cf_zones_list    = $cf_zones->result;

		if ( ! (bool) $cf_zones_list ) {
			$domains[] = __( 'No domain available in your Cloudflare account', 'rocket' );

			return $domains;
		}

		foreach ( $cf_zones_list as $cf_zone ) {
			$domains[ $cf_zone->name ] = $cf_zone->name;
		}

		return $domains;
	} catch ( Exception $e ) {
		return $domains;
	}
}


/**
 * Get CNAMES hosts
 *
 * @since 2.3
 * @deprecated 3.4
 *
 * @param  string $zones CNAMES zones.
 * @return array $hosts CNAMES hosts
 */
function get_rocket_cnames_host( $zones = array( 'all' ) ) {
	_deprecated_function( __FUNCTION__ . '()', '3.4', '\WP_Rocket\Subscriber\CDN\CDNSubscriber::get_cdn_hosts()' );
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
 * Apply CDN on CSS properties (background, background-image, @import, src:url (fonts))
 *
 * @since 2.6
 * @since 3.4
 *
 * @param  string $buffer file content.
 * @return string modified file content
 */
function rocket_cdn_css_properties( $buffer ) {
	_deprecated_function( __FUNCTION__ . '()', '3.4', '\WP_Rocket\Subscriber\CDN\CDN::rewrite_css_properties()' );

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
 * @deprecated 3.4
 *
 * @param   string $html Original Output.
 * @return  string $html Output that will be printed
 */
function rocket_add_cdn_on_custom_attr( $html ) {
	_deprecated_function( __FUNCTION__ . '()', '3.4' );
	if ( preg_match( '/(data-lazy-src|data-lazyload|data-src|data-retina)=[\'"]?([^\'"\s>]+)[\'"]/i', $html, $matches ) ) {
		$html = str_replace( $matches[2], get_rocket_cdn_url( $matches[2], array( 'all', 'images' ) ), $html );
	}

	return $html;
}


/**
 * Replace URL by CDN of all thumbnails and smilies.
 *
 * @since 2.1
 * @deprecated 3.4
 *
 * @param string $url URL of the file to replace the domain with the CDN.
 * @return string modified URL
 */
function rocket_cdn_file( $url ) {
	_deprecated_function( __FUNCTION__ . '()', '3.4' );
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
 * @deprecated 3.4
 * @author Remy Perona
 * @source https://github.com/wp-media/wp-rocket/issues/271#issuecomment-269849927
 *
 * @param array $image An array containing the src, width and height of the image.
 * @return array Array with updated src URL
 */
function rocket_cdn_attachment_image_src( $image ) {
	_deprecated_function( __FUNCTION__ . '()', '3.4' );
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
 * @deprecated 3.4
 * @author Remy Perona
 *
 * @param  array $sources multidimensional array containing srcset images urls.
 * @return array $sources
 */
function rocket_add_cdn_on_srcset( $sources ) {
	_deprecated_function( __FUNCTION__ . '()', '3.4' );
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
 * @deprecated 3.4
 *
 * @param  string $html HTML content to parse.
 * @return string modified HTML content
 */
function rocket_cdn_images( $html ) {
	_deprecated_function( __FUNCTION__ . '()', '3.4' );
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
 * @deprecated 3.4
 * @author Remy Perona
 *
 * @param  string $html HTML content of the page.
 * @return string modified HTML content
 */
function rocket_cdn_inline_styles( $html ) {
	_deprecated_function( __FUNCTION__ . '()', '3.4' );
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
 * @deprecated 3.4
 * @author Remy Perona
 *
 * @param string $html HTML content of the page.
 * @return string modified HTML content
 */
function rocket_cdn_custom_files( $html ) {
	_deprecated_function( __FUNCTION__ . '()', '3.4' );
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
 * @deprecated 3.4
 *
 * @param  string $src URL of the file.
 * @return string modified URL
 */
function rocket_cdn_enqueue( $src ) {
	_deprecated_function( __FUNCTION__ . '()', '3.4' );
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

/**
 * Get all files we don't allow to get in CDN.
 *
 * @since 2.5
 * @deprecated 3.4
 *
 * @return string A pipe-separated list of rejected files.
 */
function get_rocket_cdn_reject_files() {
	_deprecated_function( __FUNCTION__ . '()', '3.4', '\WP_Rocket\Subscriber\CDN\CDN::get_excluded_files()' );

	$files = get_rocket_option( 'cdn_reject_files', [] );

	/**
	 * Filter the rejected files.
	 *
	 * @since 2.5
	 *
	 * @param array $files List of rejected files.
	*/
	$files = (array) apply_filters( 'rocket_cdn_reject_files', $files );
	$files = array_filter( $files );
	$files = array_flip( array_flip( $files ) );

	return implode( '|', $files );
}

/**
 * Conflict with Envira Gallery: changes the URL argument if using WP Rocket CDN and Envira
 *
 * @since 2.6.5
 * @since 3.4
 *
 * @param array $args An array of arguments.
 * @return array Updated array of arguments
 */
function rocket_cdn_resize_image_args_on_envira_gallery( $args ) {
	_deprecated_function( __FUNCTION__ . '()', '3.4' );
	if ( ! isset( $args['url'] ) || (int) get_rocket_option( 'cdn' ) === 0 ) {
		return $args;
	}

	$cnames_host = array_flip( get_rocket_cnames_host() );
	$url_host    = rocket_extract_url_component( $args['url'], PHP_URL_HOST );
	$home_host   = rocket_extract_url_component( home_url(), PHP_URL_HOST );

	if ( isset( $cnames_host[ $url_host ] ) ) {
		$args['url'] = str_replace( $url_host, $home_host , $args['url'] );
	}

	return $args;
}

/**
 * Conflict with Envira Gallery: changes the resized URL if using WP Rocket CDN and Envira
 *
 * @since 2.6.5
 * @since 3.4
 *
 * @param string $url Resized image URL.
 * @return string Resized image URL using the CDN URL
 */
function rocket_cdn_resized_url_on_envira_gallery( $url ) {
	_deprecated_function( __FUNCTION__ . '()', '3.4' );
	if ( (int) get_rocket_option( 'cdn' ) === 0 ) {
		return $url;
	}

	$url = get_rocket_cdn_url( $url, array( 'all', 'images' ) );
	return $url;
}

/**
 * Apply CDN settings to Beaver Builder parallax.
 *
 * @since  3.2.1
 * @deprecated 3.4
 * @author Grégory Viguier
 *
 * @param  array $attrs HTML attributes.
 * @return array
 */
function rocket_beaver_builder_add_cdn_to_parallax( $attrs ) {
	_deprecated_function( __FUNCTION__ . '()', '3.4' );

	if ( ! empty( $attrs['data-parallax-image'] ) ) {
		$attrs['data-parallax-image'] = get_rocket_cdn_url( $attrs['data-parallax-image'], [ 'all', 'images' ] );
	}

	return $attrs;
}

if ( class_exists( 'WR2X_Admin' ) ) :
	/**
	 * Conflict with WP Retina x2: Apply CDN on srcset attribute.
	 *
	 * @since 2.9.1 Use global $wr2x_admin
	 * @since 2.5.5
	 * @deprecated 3.4
	 *
	 * @param string $url URL of the image.
	 * @return string Updated URL with CDN
	 */
	function rocket_cdn_on_images_from_wp_retina_x2( $url ) {
		_deprecated_function( __FUNCTION__ . '()', '3.4' );

		global $wr2x_admin;

		if ( ! method_exists( $wr2x_admin, 'is_pro' ) || ! $wr2x_admin->is_pro() ) {
			return $url;
		}

		$cdn_domain = get_option( 'wr2x_cdn_domain' );

		if ( ! empty( $cdn_domain ) ) {
			return $url;
		}

		return get_rocket_cdn_url( $url, array( 'all', 'images' ) );
	}
endif;

/**
 * Conflict with Avada theme and WP Rocket CDN
 *
 * @since 2.6.1
 * @deprecated 3.4
 *
 * @param array  $vars An array of variables.
 * @param string $handle Name of the avada resource.
 * @return array updated array of variables
 */
function rocket_fix_cdn_for_avada_theme( $vars, $handle ) {
	_deprecated_function( __FUNCTION__ . '()', '3.4' );
	if ( 'avada-dynamic' === $handle && get_rocket_option( 'cdn' ) ) {
		$src                        = get_rocket_cdn_url( get_template_directory_uri() . '/assets/less/theme/dynamic.less' );
		$vars['template-directory'] = sprintf( '~"%s"', dirname( dirname( dirname( dirname( $src ) ) ) ) );
		$vars['lessurl']            = sprintf( '~"%s"', dirname( $src ) );
	}
	return $vars;
}

/**
 * Conflict with Aqua Resizer & IrishMiss Framework: Apply CDN without blank src!!
 *
 * @since 2.5.8 Add compatibility with IrishMiss Framework
 * @since 2.5.5
 * @deprecated 3.4
 */
function rocket_cdn_on_aqua_resizer() {
	_deprecated_function( __FUNCTION__ . '()', '3.4' );

	if ( function_exists( 'aq_resize' ) || function_exists( 'miss_display_image' ) ) {
		remove_filter( 'wp_get_attachment_url' , 'rocket_cdn_file', PHP_INT_MAX );
		add_filter( 'rocket_lazyload_html', 'rocket_add_cdn_on_custom_attr' );
	}
}

/**
 * Conflict with Revolution Slider & Master Slider: Apply CDN on data-lazyload|data-src attribute.
 *
 * @since 2.5.5
 * @deprecated 3.4
 */
function rocket_cdn_on_sliders_with_lazyload() {
	_deprecated_function( __FUNCTION__ . '()', '3.4' );

	if ( class_exists( 'RevSliderFront' ) || class_exists( 'Master_Slider' ) ) {
		add_filter( 'rocket_cdn_images_html', 'rocket_add_cdn_on_custom_attr' );
	}
}

