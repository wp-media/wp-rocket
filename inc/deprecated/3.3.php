<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

if ( ! function_exists( 'do_rocket_callback' ) ) :
	/**
	 * The famous callback, it puts contents in a cache file if buffer length > 255 (IE do not read pages under 255 c. )
	 *
	 * @since 1.0
	 * @since 1.3.0 Add filter rocket_buffer
	 * @since 3.3.0 Deprecated
	 * @deprecated
	 *
	 * @param  string $buffer The buffer content.
	 * @return string         The buffered content.
	 */
	function do_rocket_callback( $buffer ) {
		_deprecated_function( __FUNCTION__ . '()', '3.3', '\WP_Rocket\Buffer\Cache->maybe_process_buffer()' );

		/**
		 * Allow to cache search results
		 *
		 * @since 2.3.8
		 *
		 * @param bool true will force caching search results.
		 */
		$rocket_cache_search = apply_filters( 'rocket_cache_search', false );

		/**
		 * Allow to override the DONOTCACHEPAGE behavior.
		 * To warn conflict with some plugins like Thrive Leads.
		 *
		 * @since 2.5
		 *
		 * @param bool true will force the override.
		 */
		$rocket_override_donotcachepage = apply_filters( 'rocket_override_donotcachepage', false );

		if ( strlen( $buffer ) > 255
			&& ( http_response_code() === 200 ) // only cache 200.
			&& ( function_exists( 'is_404' ) && ! is_404() ) // Don't cache 404.
			&& ( function_exists( 'is_search' ) && ! is_search() || $rocket_cache_search ) // Don't cache search results.
			&& ( ! defined( 'DONOTCACHEPAGE' ) || ! DONOTCACHEPAGE || $rocket_override_donotcachepage ) // Don't cache template that use this constant.
			&& function_exists( 'rocket_mkdir_p' )
		) {
			global $request_uri_path, $rocket_cache_filepath, $is_nginx;

			$footprint = '';
			$is_html   = false;

			if ( preg_match( '/(<\/html>)/i', $buffer ) ) {
				/**
				 * This hook is used for:
				 * - Add width and height attributes on images
				 * - Deferred JavaScript files
				 * - DNS Prefechting
				 * - Minification HTML/CSS/JavaScript
				 * - CDN
				 * - LazyLoad
				 */
				$buffer = apply_filters( 'rocket_buffer', $buffer );

				$is_html = true;
			}

			/**
			 * Allow to the generate the caching file
			 *
			 * @since 2.5
			 *
			 * @param bool true will force the caching file generation.
			 */
			if ( apply_filters( 'do_rocket_generate_caching_files', true ) ) {
				// Create cache folders of the request uri.
				rocket_mkdir_p( $request_uri_path );

				if ( $is_html ) {
					$footprint = get_rocket_footprint();
				}

				// Save the cache file.
				rocket_put_content( $rocket_cache_filepath, $buffer . $footprint );

				if ( get_rocket_option( 'do_caching_mobile_files' ) ) {
					if ( $is_nginx ) {
						// Create a hidden empty file for mobile detection on NGINX with the Rocket NGINX configuration.
						$nginx_mobile_detect_file = $request_uri_path . '/.mobile-active';

						if ( ! rocket_direct_filesystem()->exists( $nginx_mobile_detect_file ) ) {
							rocket_direct_filesystem()->touch( $nginx_mobile_detect_file );
						}
					}
				}

				if ( function_exists( 'gzencode' ) ) {
					rocket_put_content( $rocket_cache_filepath . '_gzip', gzencode( $buffer . $footprint, apply_filters( 'rocket_gzencode_level_compression', 3 ) ) );
				}

				// Send headers with the last modified time of the cache file.
				if ( file_exists( $rocket_cache_filepath ) ) {
					header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', filemtime( $rocket_cache_filepath ) ) . ' GMT' );
				}
			}

			if ( $is_html ) {
				$footprint = get_rocket_footprint( false );
			}

			$buffer = $buffer . $footprint;
		}

		return $buffer;
	}
endif;

if ( ! function_exists( 'rocket_serve_cache_file' ) ) :
	/**
	 * Serve the cache file if exist.
	 *
	 * @since 2.0
	 * @since 2.11 Serve the gzipped cache file if possible.
	 * @since 3.3.0 Deprecated
	 * @deprecated
	 *
	 * @param string $rocket_cache_filepath Path to the cache file.
	 */
	function rocket_serve_cache_file( $rocket_cache_filepath ) {
		_deprecated_function( __FUNCTION__ . '()', '3.3' );

		$rocket_cache_filepath_gzip = $rocket_cache_filepath . '_gzip';

		// Check if cache file exist.
		if ( isset( $_SERVER['HTTP_ACCEPT_ENCODING'] ) && false !== strpos( $_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip' ) && file_exists( $rocket_cache_filepath_gzip ) && is_readable( $rocket_cache_filepath_gzip ) ) {
			header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', filemtime( $rocket_cache_filepath_gzip ) ) . ' GMT' );

			// Getting If-Modified-Since headers sent by the client.
			if ( function_exists( 'apache_request_headers' ) ) {
				$headers                = apache_request_headers();
				$http_if_modified_since = ( isset( $headers['If-Modified-Since'] ) ) ? $headers['If-Modified-Since'] : '';
			} else {
				$http_if_modified_since = ( isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : '';
			}

			// Checking if the client is validating his cache and if it is current.
			if ( $http_if_modified_since && ( strtotime( $http_if_modified_since ) === @filemtime( $rocket_cache_filepath_gzip ) ) ) {
				// Client's cache is current, so we just respond '304 Not Modified'.
				header( $_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified', true, 304 );
				header( 'Expires: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
				header( 'Cache-Control: no-cache, must-revalidate' );
				exit;
			}

			// Serve the cache if file isn't store in the client browser cache.
			readgzfile( $rocket_cache_filepath_gzip );
			exit;
		}

		if ( file_exists( $rocket_cache_filepath ) && is_readable( $rocket_cache_filepath ) ) {
			header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', filemtime( $rocket_cache_filepath ) ) . ' GMT' );

			// Getting If-Modified-Since headers sent by the client.
			if ( function_exists( 'apache_request_headers' ) ) {
				$headers                = apache_request_headers();
				$http_if_modified_since = ( isset( $headers['If-Modified-Since'] ) ) ? $headers['If-Modified-Since'] : '';
			} else {
				$http_if_modified_since = ( isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : '';
			}

			// Checking if the client is validating his cache and if it is current.
			if ( $http_if_modified_since && ( strtotime( $http_if_modified_since ) === @filemtime( $rocket_cache_filepath ) ) ) {
				// Client's cache is current, so we just respond '304 Not Modified'.
				header( $_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified', true, 304 );
				header( 'Expires: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
				header( 'Cache-Control: no-cache, must-revalidate' );
				exit;
			}

			// Serve the cache if file isn't store in the client browser cache.
			readfile( $rocket_cache_filepath );
			exit;
		}
	}
endif;

if ( ! function_exists( 'rocket_define_donotoptimize_constant' ) ) :
	/**
	 * Declares and sets value of constant preventing Optimizations
	 *
	 * @since  2.11
	 * @since  3.3.0 Deprecated
	 * @author Remy Perona
	 * @deprecated
	 *
	 * @param bool $value true or false.
	 */
	function rocket_define_donotoptimize_constant( $value ) {
		_deprecated_function( __FUNCTION__ . '()', '3.3', '\WP_Rocket\Buffer\Cache->define_donotoptimize_true()' );

		if ( ! defined( 'DONOTROCKETOPTIMIZE' ) ) {
			define( 'DONOTROCKETOPTIMIZE', (bool) $value );
		}
	}
endif;

if ( ! function_exists( 'rocket_urlencode_lowercase' ) ) :
	/**
	 * Force lowercase on encoded url strings from different alphabets to prevent issues on some hostings.
	 *
	 * @since 2.7
	 * @since 3.3.0 Deprecated
	 * @deprecated
	 *
	 * @param string $matches Cache path.
	 * @return string cache path in lowercase
	 */
	function rocket_urlencode_lowercase( $matches ) {
		_deprecated_function( __FUNCTION__ . '()', '3.3', '\WP_Rocket\Buffer\Cache->reset_lowercase()' );

		return strtolower( $matches[0] );
	}
endif;

if ( ! function_exists( 'rocket_get_ip' ) ) :
	/**
	 * Get the IP address from which the user is viewing the current page.
	 *
	 * @since 2.7.3
	 * @since 3.3.0 Deprecated
	 * @deprecated
	 */
	function rocket_get_ip() {
		_deprecated_function( __FUNCTION__ . '()', '3.3', '\WP_Rocket\Buffer\Tests->get_ip()' );

		$keys = array(
			'HTTP_CF_CONNECTING_IP', // CF = CloudFlare.
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_X_REAL_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		);

		foreach ( $keys as $key ) {
			if ( array_key_exists( $key, $_SERVER ) ) {
				$ip = explode( ',', $_SERVER[ $key ] );
				$ip = end( $ip );

				if ( false !== filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					return $ip;
				}
			}
		}

		return '0.0.0.0';
	}
endif;

if ( ! function_exists( 'get_rocket_footprint' ) ) :
	/**
	 * Get WP Rocket footprint
	 *
	 * @deprecated 3.3
	 * @since 3.0.5 White label footprint if WP_ROCKET_WHITE_LABEL_FOOTPRINT is defined.
	 * @since 2.0
	 *
	 * @param bool $debug (default: true) If true, adds the date of generation cache file.
	 * @return string The footprint that will be printed
	 */
	function get_rocket_footprint( $debug = true ) {
		_deprecated_function( __FUNCTION__ . '()', '3.3', '\WP_Rocket\Buffer\Cache->get_rocket_footprint()' );
		$footprint = defined( 'WP_ROCKET_WHITE_LABEL_FOOTPRINT' ) ?
						"\n" . '<!-- Cached for great performance' :
						"\n" . '<!-- This website is like a Rocket, isn\'t it? Performance optimized by ' . WP_ROCKET_PLUGIN_NAME . '. Learn more: https://wp-rocket.me';
		if ( $debug ) {
			$footprint .= ' - Debug: cached@' . time();
		}
		$footprint .= ' -->';
		return $footprint;
	}
endif;

if ( ! function_exists( 'rocket_lazyload_script' ) ) :
/**
 * Add lazyload options to the footer
 *
 * @deprecated 3.3
 * @since 2.11 load options in the footer and add filter for the treshold
 * @since 1.3.5 It's possible to exclude LazyLoad process by used do_rocket_lazyload filter
 * @since 1.1.0 This code is insert in head with inline script for more performance
 * @since 1.0
 */
function rocket_lazyload_script() {
	_deprecated_function( __FUNCTION__ . '()', '3.3', '\RocketLazyload\Assets::insertLazyloadScript()' );
	if ( ( defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) ) {
		return;
	}

	if ( ( ! get_rocket_option( 'lazyload' ) && ! get_rocket_option( 'lazyload_iframes' ) ) || ( ! apply_filters( 'do_rocket_lazyload', true ) && ! apply_filters( 'do_rocket_lazyload_iframes', true ) ) ) {
		return;
	}

	$suffix   = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	$elements = [];

	if ( get_rocket_option( 'lazyload' ) ) {
		$elements[] = 'img';
	}

	if ( get_rocket_option( 'lazyload_iframes' ) ) {
		$elements[] = 'iframe';
	}

	/**
	 * Filters the threshold at which lazyload is triggered
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @param int $threshold Threshold value.
	 */
	$threshold = apply_filters( 'rocket_lazyload_threshold', 300 );

	echo '<script>(function(w, d){
	var b = d.getElementsByTagName("body")[0];
	var s = d.createElement("script"); s.async = true;
	s.src = !("IntersectionObserver" in w) ? "' . get_rocket_cdn_url( WP_ROCKET_FRONT_JS_URL, array( 'all', 'css_and_js', 'js' ) ) . 'lazyload-8.15.2' . $suffix . '.js" : "' . get_rocket_cdn_url( WP_ROCKET_FRONT_JS_URL, array( 'all', 'css_and_js', 'js' ) ) . 'lazyload-10.17' . $suffix . '.js";
	w.lazyLoadOptions = {
		elements_selector: "' . esc_attr( implode( ',', $elements ) ) . '",
		data_src: "lazy-src",
		data_srcset: "lazy-srcset",
		data_sizes: "lazy-sizes",
		skip_invisible: false,
		class_loading: "lazyloading",
		class_loaded: "lazyloaded",
		threshold: ' . esc_attr( $threshold ) . ',
		callback_load: function(element) {
			if ( element.tagName === "IFRAME" && element.dataset.rocketLazyload == "fitvidscompatible" ) {
				if (element.classList.contains("lazyloaded") ) {
					if (typeof window.jQuery != "undefined") {
						if (jQuery.fn.fitVids) {
							jQuery(element).parent().fitVids();
						}
					}
				}
			}
		}
	}; // Your options here. See "recipes" for more information about async.
	b.appendChild(s);
}(window, document));

// Listen to the Initialized event
window.addEventListener(\'LazyLoad::Initialized\', function (e) {
    // Get the instance and puts it in the lazyLoadInstance variable
	var lazyLoadInstance = e.detail.instance;

	var observer = new MutationObserver(function(mutations) {
		mutations.forEach(function(mutation) {
			lazyLoadInstance.update();
		} );
	} );

	var b      = document.getElementsByTagName("body")[0];
	var config = { childList: true, subtree: true };

	observer.observe(b, config);
}, false);
</script>';

	if ( get_rocket_option( 'lazyload_youtube' ) ) {
		/**
		 * Filters the resolution of the YouTube thumbnail
		 *
		 * @since 2.11.5
		 * @author Arun Basil Lal
		 *
		 * @param string $thumbnail_resolution The resolution of the thumbnail. Accepted values: default, mqdefault, sddefault, hqdefault, maxresdefault
		 */
		$thumbnail_resolution = apply_filters( 'rocket_youtube_thumbnail_resolution', 'hqdefault' );

		echo <<<HTML
		<script>function lazyLoadThumb(e){var t='<img src="https://i.ytimg.com/vi/ID/$thumbnail_resolution.jpg">',a='<div class="play"></div>';return t.replace("ID",e)+a}function lazyLoadYoutubeIframe(){var e=document.createElement("iframe"),t="https://www.youtube.com/embed/ID?autoplay=1";t+=0===this.dataset.query.length?'':'&'+this.dataset.query;e.setAttribute("src",t.replace("ID",this.dataset.id)),e.setAttribute("frameborder","0"),e.setAttribute("allowfullscreen","1"),this.parentNode.replaceChild(e,this)}document.addEventListener("DOMContentLoaded",function(){var e,t,a=document.getElementsByClassName("rll-youtube-player");for(t=0;t<a.length;t++)e=document.createElement("div"),e.setAttribute("data-id",a[t].dataset.id),e.setAttribute("data-query", a[t].dataset.query),e.innerHTML=lazyLoadThumb(a[t].dataset.id),e.onclick=lazyLoadYoutubeIframe,a[t].appendChild(e)});</script>
HTML;
	}
}
endif;

if ( ! function_exists( 'rocket_lazyload_enqueue' ) ) :
/**
 * Enqueue the CSS code for Youtube lazyload styling
 *
 * @deprecated 3.3
 * @since 2.11
 * @author Remy Perona
 */
function rocket_lazyload_enqueue() {
	_deprecated_function( __FUNCTION__ . '()', '3.3', '\RocketLazyload\Assets::insertYoutubeThumbnailCSS()' );
	if ( ( defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) ) {
		return;
	}

	if ( ( ! get_rocket_option( 'lazyload_iframes' ) ) || ( ! apply_filters( 'do_rocket_lazyload', true ) && ! apply_filters( 'do_rocket_lazyload_iframes', true ) ) ) {
		return;
	}

	if ( get_rocket_option( 'lazyload_youtube' ) ) {
		$css = '.rll-youtube-player{position:relative;padding-bottom:56.23%;height:0;overflow:hidden;max-width:100%;background:#000;margin:5px}.rll-youtube-player iframe{position:absolute;top:0;left:0;width:100%;height:100%;z-index:100;background:0 0}.rll-youtube-player img{bottom:0;display:block;left:0;margin:auto;max-width:100%;width:100%;position:absolute;right:0;top:0;border:none;height:auto;cursor:pointer;-webkit-transition:.4s all;-moz-transition:.4s all;transition:.4s all}.rll-youtube-player img:hover{-webkit-filter:brightness(75%)}.rll-youtube-player .play{height:72px;width:72px;left:50%;top:50%;margin-left:-36px;margin-top:-36px;position:absolute;background:url(' . WP_ROCKET_FRONT_URL . 'img/youtube.png) no-repeat;cursor:pointer}';

		wp_register_style( 'rocket-lazyload', false );
		wp_enqueue_style( 'rocket-lazyload' );
		wp_add_inline_style( 'rocket-lazyload', $css );
	}
}
endif;

if ( ! function_exists( 'rocket_lazyload_images' ) ) :
/**
 * Replace Gravatar, thumbnails, images in post content and in widget text by LazyLoad
 *
 * @deprecated 3.3
 * @since 2.6 Add the get_image_tag filter
 * @since 2.2 Better regex pattern in a replace_callback
 * @since 1.3.5 It's possible to exclude LazyLoad process by used do_rocket_lazyload filter
 * @since 1.2.0 It's possible to not lazyload an image with data-no-lazy attribute
 * @since 1.1.0 Don't lazyload if the thumbnail has already been run through previously
 * @since 1.0.1 Add priority of hooks at maximum later with PHP_INT_MAX
 * @since 1.0
 *
 * @param string $html HTML content.
 * @return string Modified HTML content
 */
function rocket_lazyload_images( $html ) {
	_deprecated_function( __FUNCTION__ . '()', '3.3', '\RocketLazyload\Images::lazyloadImages()' );
	// Don't LazyLoad if process is stopped for these reasons.
	if ( ! get_rocket_option( 'lazyload' ) || ! apply_filters( 'do_rocket_lazyload', true ) || is_feed() || is_preview() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || ( defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) || empty( $html ) || ( defined( 'DONOTLAZYLOAD' ) && DONOTLAZYLOAD ) || wp_script_is( 'twentytwenty-twentytwenty', 'enqueued' ) ) {
		return $html;
	}

	$html = preg_replace_callback( '#<img([^>]*) src=("(?:[^"]+)"|\'(?:[^\']+)\'|(?:[^ >]+))([^>]*)>#', 'rocket_lazyload_replace_callback', $html );

	return $html;
}
endif;

if ( ! function_exists( 'rocket_lazyload_replace_callback' ) ) :
/**
 * Used to check if we have to LazyLoad this or not
 *
 * @deprecated 3.3
 * @since 2.5.5  Don't apply LazyLoad on images from WP Retina x2
 * @since 2.5    Don't apply LazyLoad on all images from LayerSlider
 * @since 2.4.2  Don't apply LazyLoad on all images from Media Grid
 * @since 2.3.11 Don't apply LazyLoad on all images from Timthumb
 * @since 2.3.10 Don't apply LazyLoad on all images from Revolution Slider & Justified Image Grid
 * @since 2.3.8  Don't apply LazyLoad on captcha from Really Simple CAPTCHA
 * @since 2.2
 *
 * @param array $matches Images matching the regex.
 * @return string Modified HTML content
 */
function rocket_lazyload_replace_callback( $matches ) {
	_deprecated_function( __FUNCTION__ . '()', '3.3', '\RocketLazyload\Images::lazyloadImages()' );
	// Don't apply LazyLoad on images from WP Retina x2.
	if ( function_exists( 'wr2x_picture_rewrite' ) ) {
		if ( wr2x_get_retina( trailingslashit( ABSPATH ) . wr2x_get_pathinfo_from_image_src( trim( $matches[2], '"' ) ) ) ) {
			return $matches[0];
		}
	}

	/**
	 * Filters the attributes used to prevent lazylad from being applied
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @param array $excluded_attributes An array of excluded attributes.
	 */
	$excluded_attributes = apply_filters(
		'rocket_lazyload_excluded_attributes', array(
			'data-src=',
			'data-no-lazy=',
			'data-lazy-original=',
			'data-lazy-src=',
			'data-lazysrc=',
			'data-lazyload=',
			'data-bgposition=',
			'data-envira-src=',
			'fullurl=',
			'lazy-slider-img=',
			'data-srcset=',
			'class="ls-l',
			'class="ls-bg',
		)
	);

	/**
	 * Filters the src used to prevent lazylad from being applied
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @param array $excluded_src An array of excluded src.
	 */
	$excluded_src = apply_filters(
		'rocket_lazyload_excluded_src', array(
			'/wpcf7_captcha/',
			'timthumb.php?src',
		)
	);

	if ( rocket_is_excluded_lazyload( $matches[1] . $matches[3], $excluded_attributes ) || rocket_is_excluded_lazyload( $matches[2], $excluded_src ) ) {
		return $matches[0];
	}

	/**
	 * Filter the LazyLoad placeholder on src attribute
	 *
	 * @since 1.1
	 *
	 * @param string $placeholder Placeholder that will be printed.
	 */
	$placeholder = apply_filters( 'rocket_lazyload_placeholder', 'data:image/gif;base64,R0lGODdhAQABAPAAAP///wAAACwAAAAAAQABAEACAkQBADs=' );

	$html = sprintf( '<img%1$s src="%4$s" data-lazy-src=%2$s%3$s>', $matches[1], $matches[2], $matches[3], $placeholder );

	$html_noscript = sprintf( '<noscript><img%1$s src=%2$s%3$s></noscript>', $matches[1], $matches[2], $matches[3] );

	/**
	 * Filter the LazyLoad HTML output
	 *
	 * @since 1.0.2
	 *
	 * @param array $html Output that will be printed
	 */
	$html = apply_filters( 'rocket_lazyload_html', $html, true );

	return $html . $html_noscript;
}
endif;

if ( ! function_exists( 'rocket_is_excluded_lazyload' ) ) :
/**
 * Determine if the current image should be excluded from lazyload
 *
 * @deprecated 3.3
 * @since 1.1
 * @author Remy Perona
 *
 * @param string $string String to search.
 * @param array  $excluded_values Array of excluded values to search in the string.
 * @return bool True if one of the excluded values was found, false otherwise
 */
function rocket_is_excluded_lazyload( $string, $excluded_values ) {
	_deprecated_function( __FUNCTION__ . '()', '3.3', '\RocketLazyload\Images::isExcluded()' );
	foreach ( $excluded_values as $excluded_value ) {
		if ( strpos( $string, $excluded_value ) !== false ) {
			return true;
		}
	}

	return false;
}
endif;

if ( ! function_exists( 'rocket_lazyload_smilies' ) ) :
/**
 * Replace WordPress smilies by Lazy Load
 *
 * @since 3.3
 * @since 2.0   New system for replace smilies by Lazy Load
 * @since 1.3.5 It's possible to exclude LazyLoad process by used do_rocket_lazyload filter
 * @since 1.1.0 Don't lazy-load if the thumbnail has already been run through previously
 * @since 1.0.1 Add priority of hooks at maximum later with PHP_INT_MAX
 * @since 1.0
 */
function rocket_lazyload_smilies() {
	_deprecated_function( __FUNCTION__ . '()', '3.3', '\WP_Rocket\Subscriber\Optimization\Lazyload_Subscriber::lazyload_smilies()' );
	if ( ! get_rocket_option( 'lazyload' ) || ! apply_filters( 'do_rocket_lazyload', true, 'smilies' ) || ( defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) || ( defined( 'DONOTLAZYLOAD' ) && DONOTLAZYLOAD ) ) {
		return;
	}

	remove_filter( 'the_content', 'convert_smilies' );
	remove_filter( 'the_excerpt', 'convert_smilies' );
	remove_filter( 'comment_text', 'convert_smilies', 20 );

	add_filter( 'the_content', 'rocket_convert_smilies' );
	add_filter( 'the_excerpt', 'rocket_convert_smilies' );
	add_filter( 'comment_text', 'rocket_convert_smilies', 20 );
}
endif;

if ( ! function_exists( 'rocket_convert_smilies' ) ) :
/**
 * Convert text equivalent of smilies to images.
 *
 * @source convert_smilies() in /wp-includes/formattings.php
 * @since 2.0
 * @deprecated 3.3
 * @param string $text Text to process.
 * @return string Modified text
 */
function rocket_convert_smilies( $text ) {
	_deprecated_function( __FUNCTION__ . '()', '3.3', '\RocketLazyload\Image::convertSmilies()' );
	global $wp_smiliessearch;

	if ( ! get_option( 'use_smilies' ) || empty( $wp_smiliessearch ) ) {
		return $text;
	}

	$output = '';
	// HTML loop taken from texturize function, could possible be consolidated.
	$textarr = preg_split( '/(<.*>)/U', $text, -1, PREG_SPLIT_DELIM_CAPTURE ); // capture the tags as well as in between.
	$stop    = count( $textarr );// loop stuff.

	// Ignore proessing of specific tags.
	$tags_to_ignore       = 'code|pre|style|script|textarea';
	$ignore_block_element = '';

	for ( $i = 0; $i < $stop; $i++ ) {
		$content = $textarr[ $i ];

		// If we're in an ignore block, wait until we find its closing tag.
		if ( '' === $ignore_block_element && preg_match( '/^<(' . $tags_to_ignore . ')>/', $content, $matches ) ) {
			$ignore_block_element = $matches[1];
		}

		// If it's not a tag and not in ignore block.
		if ( '' === $ignore_block_element && strlen( $content ) > 0 && '<' !== $content[0] ) {
			$content = preg_replace_callback( $wp_smiliessearch, 'rocket_translate_smiley', $content );
		}

		// did we exit ignore block.
		if ( '' !== $ignore_block_element && '</' . $ignore_block_element . '>' === $content ) {
			$ignore_block_element = '';
		}

		$output .= $content;
	}

	return $output;
}
endif;

if ( ! function_exists( 'rocket_translate_smiley' ) ) :
/**
 * Convert one smiley code to the icon graphic file equivalent.
 *
 * @source translate_smiley() in /wp-includes/formattings.php
 * @since 2.0
 * @deprecated 3.3
 *
 * @param array $matches An array of matching content.
 * @return string HTML code for smiley
 */
function rocket_translate_smiley( $matches ) {
	_deprecated_function( __FUNCTION__ . '()', '3.3', '\RocketLazyload\Image::translateSmiley()' );
	global $wpsmiliestrans;

	if ( count( $matches ) === 0 ) {
		return '';
	}

	$smiley = trim( reset( $matches ) );
	$img    = $wpsmiliestrans[ $smiley ];

	$matches    = array();
	$ext        = preg_match( '/\.([^.]+)$/', $img, $matches ) ? strtolower( $matches[1] ) : false;
	$image_exts = array( 'jpg', 'jpeg', 'jpe', 'gif', 'png' );

	// Don't convert smilies that aren't images - they're probably emoji.
	if ( ! in_array( $ext, $image_exts, true ) ) {
		return $img;
	}

	/**
	 * Filter the Smiley image URL before it's used in the image element.
	 *
	 * @since 2.9.0
	 *
	 * @param string $smiley_url URL for the smiley image.
	 * @param string $img        Filename for the smiley image.
	 * @param string $site_url   Site URL, as returned by site_url().
	 */
	$src_url = apply_filters( 'smilies_src', includes_url( "images/smilies/$img" ), $img, site_url() );

	// Don't LazyLoad if process is stopped for these reasons.
	if ( is_feed() || is_preview() ) {
		return sprintf( ' <img src="%s" alt="%s" class="wp-smiley" /> ', esc_url( $src_url ), esc_attr( $smiley ) );
	}

	/** This filter is documented in inc/front/lazyload.php */
	$placeholder = apply_filters( 'rocket_lazyload_placeholder', 'data:image/gif;base64,R0lGODdhAQABAPAAAP///wAAACwAAAAAAQABAEACAkQBADs=' );

	return sprintf( ' <img src="%s" data-lazy-src="%s" alt="%s" class="wp-smiley" /> ', $placeholder, esc_url( $src_url ), esc_attr( $smiley ) );
}
endif;

if ( ! function_exists( 'rocket_lazyload_iframes' ) ) :
/**
 * Replace iframes by LazyLoad
 *
 * @deprecated 3.3
 * @since 2.6
 *
 * @param string $html HTML content.
 * @return string Modified HTML content
 */
function rocket_lazyload_iframes( $html ) {
	_deprecated_function( __FUNCTION__ . '()', '3.3', '\RocketLazyload\Iframe::lazyloadIframes()' );
	// Don't LazyLoad if process is stopped for these reasons.
	if ( ! get_rocket_option( 'lazyload_iframes' ) || ! apply_filters( 'do_rocket_lazyload_iframes', true ) || is_feed() || is_preview() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || empty( $html ) || ( defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) || ( defined( 'DONOTLAZYLOAD' ) && DONOTLAZYLOAD ) ) {
		return $html;
	}

	preg_match_all( '@<iframe(?<atts>\s.+)>.*</iframe>@iUs', $html, $matches, PREG_SET_ORDER );

	if ( empty( $matches ) ) {
		return $html;
	}

	foreach ( $matches as $iframe ) {
		// Don't mess with the Gravity Forms ajax iframe.
		if ( strpos( $iframe[0], 'gform_ajax_frame' ) ) {
			continue;
		}

		// Don't lazyload if iframe has data-no-lazy attribute.
		if ( strpos( $iframe[0], 'data-no-lazy=' ) ) {
			continue;
		}

		// Don't lazyload if iframe is google recaptcha fallback.
		if ( strpos( $iframe[0], 'recaptcha/api/fallback' ) ) {
			continue;
		}

		// Given the previous regex pattern, $iframe['atts'] starts with a whitespace character.
		if ( ! preg_match( '@\ssrc\s*=\s*(\'|")(?<src>.*)\1@iUs', $iframe['atts'], $atts ) ) {
			continue;
		}

		$iframe['src'] = trim( $atts['src'] );

		if ( '' === $iframe['src'] ) {
			continue;
		}

		if ( get_rocket_option( 'lazyload_youtube' ) ) {
			$youtube_id = rocket_lazyload_get_youtube_id_from_url( $iframe['src'] );

			if ( $youtube_id ) {
				$query = wp_parse_url( htmlspecialchars_decode( $iframe['src'] ), PHP_URL_QUERY );

				/**
				 * Filter the LazyLoad HTML output on Youtube iframes
				 *
				 * @since 2.11
				 *
				 * @param array $html Output that will be printed.
				 */
				$youtube_lazyload  = apply_filters( 'rocket_lazyload_youtube_html', '<div class="rll-youtube-player" data-id="' . esc_attr( $youtube_id ) . '" data-query="' . esc_attr( $query ) . '"></div>' );
				$youtube_lazyload .= '<noscript>' . $iframe[0] . '</noscript>';

				$html = str_replace( $iframe[0], $youtube_lazyload, $html );
				continue;
			}
		}

		/**
		 * Filter the LazyLoad placeholder on src attribute
		 *
		 * @since 2.11
		 *
		 * @param string $placeholder placeholder that will be printed.
		 */
		$placeholder = apply_filters( 'rocket_lazyload_placeholder', 'about:blank' );

		$placeholder_atts = str_replace( $iframe['src'], $placeholder, $iframe['atts'] );
		$iframe_lazyload  = str_replace( $iframe['atts'], $placeholder_atts . ' data-rocket-lazyload="fitvidscompatible" data-lazy-src="' . esc_url( $iframe['src'] ) . '"', $iframe[0] );

		/**
		 * Filter the LazyLoad HTML output on iframes
		 *
		 * @since 2.11
		 *
		 * @param array $html Output that will be printed.
		 */
		$iframe_lazyload  = apply_filters( 'rocket_lazyload_iframe_html', $iframe_lazyload );
		$iframe_lazyload .= '<noscript>' . $iframe[0] . '</noscript>';

		$html = str_replace( $iframe[0], $iframe_lazyload, $html );
	}

	return $html;
}
endif;

if ( ! function_exists( 'rocket_deactivate_lazyload_on_specific_posts' ) ) :
/**
 * Check if we need to exclude LazyLoad on specific posts
 *
 * @since 3.3
 * @since 2.5
 */
function rocket_deactivate_lazyload_on_specific_posts() {
	_deprecated_function( __FUNCTION__ . '()', '3.3', '\WP_Rocket\Subscriber\Optimization\Lazyload_Subscriber::deactivate_lazyload_on_specific_posts()' );
	if ( is_rocket_post_excluded_option( 'lazyload' ) ) {
		add_filter( 'do_rocket_lazyload', '__return_false' );
	}

	if ( is_rocket_post_excluded_option( 'lazyload_iframes' ) ) {
		add_filter( 'do_rocket_lazyload_iframes', '__return_false' );
	}
}
endif;

if ( ! function_exists( 'rocket_lazyload_on_srcset' ) ) :
/**
 * Compatibility with images with srcset attribute
 *
 * @author Remy Perona
 *
 * @since 3.3
 * @since 2.8 Also add sizes to the data-lazy-* attributes to prevent error in W3C validator
 * @since 2.7
 *
 * @param string $html HTML content.
 * @return string Modified HTML content
 */
function rocket_lazyload_on_srcset( $html ) {
	_deprecated_function( __FUNCTION__ . '()', '3.3', '\RocketLazyload\Image:lazyloadResponsiveAttributes()' );
	if ( preg_match( '/srcset=("(?:[^"]+)"|\'(?:[^\']+)\'|(?:[^ >]+))/i', $html ) ) {
		$html = str_replace( 'srcset=', 'data-lazy-srcset=', $html );
	}

	if ( preg_match( '/sizes=("(?:[^"]+)"|\'(?:[^\']+)\'|(?:[^ >]+))/i', $html ) ) {
		$html = str_replace( 'sizes=', 'data-lazy-sizes=', $html );
	}

	return $html;
}
endif;

if ( ! function_exists( 'rocket_lazyload_get_youtube_id_from_url' ) ) :
/**
 * Gets youtube video ID from URL
 *
 * @author Remy Perona
 * @deprecated 3.3
 * @since 2.11
 *
 * @param string $url URL to parse.
 * @return string     Youtube video id or false if none found.
 */
function rocket_lazyload_get_youtube_id_from_url( $url ) {
	_deprecated_function( __FUNCTION__ . '()', '3.3', '\RocketLazyload\Iframe:getYoutubeIDFromURL()' );
	$pattern = '#^(?:https?:)?(?://)?(?:www\.)?(?:youtu\.be|youtube\.com|youtube-nocookie\.com)/(?:embed/|v/|watch/?\?v=)([\w-]{11})#iU';
	$result  = preg_match( $pattern, $url, $matches );

	if ( ! $result ) {
		return false;
	}

	if ( 'videoseries' === $matches[1] ) {
		return false;
	}

	return $matches[1];
}
endif;
