<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

/**
 * Add Lazy Load JavaScript in the header
 * No jQuery or other library is required !!
 *
 * @since 1.3.5 It's possible to exclude LazyLoad process by used do_rocket_lazyload filter
 * @since 1.1.0 This code is insert in head with inline script for more performance
 * @since 1.0
 */
function rocket_lazyload_script() {
	if ( ( ! get_rocket_option( 'lazyload' ) && ! get_rocket_option( 'lazyload_iframes' ) ) || ( ! apply_filters( 'do_rocket_lazyload', true ) && ! apply_filters( 'do_rocket_lazyload_iframes', true ) ) ) {
		return;
	}

	$suffix       = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	$lazyload_url = get_rocket_cdn_url( WP_ROCKET_FRONT_JS_URL . 'lazyload.' . WP_ROCKET_LAZYLOAD_JS_VERSION . $suffix . '.js', array( 'all', 'css_and_js', 'js' ) );

	echo '<script data-no-minify="1" data-cfasync="false">(function(w,d){function a(){var b=d.createElement("script");b.async=!0;b.src="' . $lazyload_url . '";var a=d.getElementsByTagName("script")[0];a.parentNode.insertBefore(b,a)}w.attachEvent?w.attachEvent("onload",a):w.addEventListener("load",a,!1)})(window,document);</script>';
}
add_action( 'wp_head', 'rocket_lazyload_script', PHP_INT_MAX );

/**
 * Replace Gravatar, thumbnails, images in post content and in widget text by LazyLoad
 *
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
	// Don't LazyLoad if process is stopped for these reasons.
	if ( ! get_rocket_option( 'lazyload' ) || ! apply_filters( 'do_rocket_lazyload', true ) || is_feed() || is_preview() || empty( $html ) || ( defined( 'DONOTLAZYLOAD' ) && DONOTLAZYLOAD ) || wp_script_is( 'twentytwenty-twentytwenty', 'enqueued' ) ) {
		return $html;
	}

	$html = preg_replace_callback( '#<img([^>]*) src=("(?:[^"]+)"|\'(?:[^\']+)\'|(?:[^ >]+))([^>]*)>#', 'rocket_lazyload_replace_callback', $html );

	return $html;
}
add_filter( 'get_avatar'			, 'rocket_lazyload_images', PHP_INT_MAX );
add_filter( 'the_content'			, 'rocket_lazyload_images', PHP_INT_MAX );
add_filter( 'widget_text'			, 'rocket_lazyload_images', PHP_INT_MAX );
add_filter( 'get_image_tag'			, 'rocket_lazyload_images', PHP_INT_MAX );
add_filter( 'post_thumbnail_html'	, 'rocket_lazyload_images', PHP_INT_MAX );

/**
 * Used to check if we have to LazyLoad this or not
 *
 * @since 2.5.5	 Don't apply LazyLoad on images from WP Retina x2
 * @since 2.5	 Don't apply LazyLoad on all images from LayerSlider
 * @since 2.4.2	 Don't apply LazyLoad on all images from Media Grid
 * @since 2.3.11 Don't apply LazyLoad on all images from Timthumb
 * @since 2.3.10 Don't apply LazyLoad on all images from Revolution Slider & Justified Image Grid
 * @since 2.3.8  Don't apply LazyLoad on captcha from Really Simple CAPTCHA
 * @since 2.2
 *
 * @param array $matches Images matching the regex.
 * @return string Modified HTML content
 */
function rocket_lazyload_replace_callback( $matches ) {
	// Don't apply LazyLoad on images from WP Retina x2.
	if ( function_exists( 'wr2x_picture_rewrite' ) ) {
		if ( wr2x_get_retina( trailingslashit( ABSPATH ) . wr2x_get_pathinfo_from_image_src( trim( $matches[2], '"' ) ) ) ) {
			return $matches[0];
		}
	}

	// TO DO - improve this code with a preg_match - it's ugly!!!!
	if ( strpos( $matches[1] . $matches[3], 'data-no-lazy=' ) === false && strpos( $matches[1] . $matches[3], 'data-lazy-original=' ) === false && strpos( $matches[1] . $matches[3], 'data-lazy-src=' ) === false && strpos( $matches[1] . $matches[3], 'data-lazysrc=' ) === false && strpos( $matches[1] . $matches[3], 'data-src=' ) === false && strpos( $matches[1] . $matches[3], 'data-lazyload=' ) === false && strpos( $matches[1] . $matches[3], 'data-bgposition=' ) === false && strpos( $matches[2], '/wpcf7_captcha/' ) === false && strpos( $matches[2], 'timthumb.php?src' ) === false && strpos( $matches[1] . $matches[3], 'data-envira-src=' ) === false && strpos( $matches[1] . $matches[3], 'fullurl=' ) === false && strpos( $matches[1] . $matches[3], 'lazy-slider-img=' ) === false && strpos( $matches[1] . $matches[3], 'data-srcset=' ) === false && strpos( $matches[1] . $matches[3], 'class="ls-l' ) === false && strpos( $matches[1] . $matches[3], 'class="ls-bg' ) === false ) {

		/**
		 * Filter the LazyLoad placeholder on src attribute
		 *
		 * @since 2.6
		 *
		 * @param string Output that will be printed
		*/
		$placeholder = apply_filters( 'rocket_lazyload_placeholder', 'data:image/gif;base64,R0lGODdhAQABAPAAAP///wAAACwAAAAAAQABAEACAkQBADs=' );

		$html = sprintf( '<img%1$s src="%4$s" data-lazy-src=%2$s%3$s>', $matches[1], $matches[2], $matches[3], $placeholder );

		$html_noscript = sprintf( '<noscript><img%1$s src=%2$s%3$s></noscript>', $matches[1], $matches[2], $matches[3] );

		/**
		 * Filter the LazyLoad HTML output on images
		 *
		 * @since 2.3.8
		 *
		 * @param string $html Output that will be printed
		*/
		$html = apply_filters( 'rocket_lazyload_html', $html, true );

		return $html . $html_noscript;
	} else {
		return $matches[0];
	}
}

/**
 * Replace WordPress smilies by Lazy Load
 *
 * @since 2.0 	New system for replace smilies by Lazy Load
 * @since 1.3.5 It's possible to exclude LazyLoad process by used do_rocket_lazyload filter
 * @since 1.1.0 Don't lazy-load if the thumbnail has already been run through previously
 * @since 1.0.1 Add priority of hooks at maximum later with PHP_INT_MAX
 * @since 1.0
 */
function rocket_lazyload_smilies() {
	if ( ! get_rocket_option( 'lazyload' ) || ! apply_filters( 'do_rocket_lazyload', true, 'smilies' ) || ( defined( 'DONOTLAZYLOAD' ) && DONOTLAZYLOAD ) ) {
		return;
	}

	remove_filter( 'the_content', 'convert_smilies' );
	remove_filter( 'the_excerpt', 'convert_smilies' );
	remove_filter( 'comment_text', 'convert_smilies', 20 );

	add_filter( 'the_content', 'rocket_convert_smilies' );
	add_filter( 'the_excerpt', 'rocket_convert_smilies' );
	add_filter( 'comment_text', 'rocket_convert_smilies', 20 );
}
add_action( 'init', 'rocket_lazyload_smilies' );

/**
 * Convert text equivalent of smilies to images.
 *
 * @source convert_smilies() in /wp-includes/formattings.php
 * @since 2.0
 *
 * @param string $text Text to process.
 * @return string Modified text
 */
function rocket_convert_smilies( $text ) {
	global $wp_smiliessearch;

	$output = '';
	if ( get_option( 'use_smilies' ) && ! empty( $wp_smiliessearch ) ) {
		// HTML loop taken from texturize function, could possible be consolidated.
		$textarr = preg_split( '/(<.*>)/U', $text, -1, PREG_SPLIT_DELIM_CAPTURE ); // capture the tags as well as in between.
		$stop = count( $textarr );// loop stuff.

		// Ignore proessing of specific tags.
		$tags_to_ignore = 'code|pre|style|script|textarea';
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
	} else {
		// return default text.
		$output = $text;
	}
	return $output;
}

/**
 * Convert one smiley code to the icon graphic file equivalent.
 *
 * @source translate_smiley() in /wp-includes/formattings.php
 * @since 2.0
 *
 * @param array $matches An array of matching content.
 * @return string HTML code for smiley
 */
function rocket_translate_smiley( $matches ) {
	global $wpsmiliestrans;

	if ( count( $matches ) === 0 ) {
		return '';
	}

	$smiley = trim( reset( $matches ) );
	$img = $wpsmiliestrans[ $smiley ];

	$matches = array();
	$ext = preg_match( '/\.([^.]+)$/', $img, $matches ) ? strtolower( $matches[1] ) : false;
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
	if ( ! is_feed() && ! is_preview() ) {

		/** This filter is documented in inc/front/lazyload.php */
		$placeholder = apply_filters( 'rocket_lazyload_placeholder', 'data:image/gif;base64,R0lGODdhAQABAPAAAP///wAAACwAAAAAAQABAEACAkQBADs=' );

		return sprintf( ' <img src="%s" data-lazy-src="%s" alt="%s" class="wp-smiley" /> ', $placeholder, esc_url( $src_url ), esc_attr( $smiley ) );

	} else {

		return sprintf( ' <img src="%s" alt="%s" class="wp-smiley" /> ', esc_url( $src_url ), esc_attr( $smiley ) );

	}
}

/**
 * Replace iframes by LazyLoad
 *
 * @since 2.6
 *
 * @param string $html HTML content.
 * @return string Modified HTML content
 */
function rocket_lazyload_iframes( $html ) {
	// Don't LazyLoad if process is stopped for these reasons.
	if ( ! get_rocket_option( 'lazyload_iframes' ) || ! apply_filters( 'do_rocket_lazyload_iframes', true ) || is_feed() || is_preview() || empty( $html ) || ( defined( 'DONOTLAZYLOAD' ) && DONOTLAZYLOAD ) ) {
		return $html;
	}

	$matches = array();
	preg_match_all( '/<iframe\s+.*?>/', $html, $matches );

	foreach ( $matches[0] as $k => $iframe ) {

		// Don't mess with the Gravity Forms ajax iframe.
		if ( strpos( $iframe, 'gform_ajax_frame' ) ) {
			continue;
		}

		// Don't lazyload if iframe has data-no-lazy attribute.
		if ( strpos( $iframe, 'data-no-lazy=' ) ) {
			continue;
		}

		/**
		 * Filters the placeholder for iframe lazyload
		 *
		 * @param string $placeholder Placeholder for iframe lazyload.
		 */
		$placeholder = apply_filters( 'rocket_iframe_lazyload_placeholder', 'about:blank' );

		$iframe = preg_replace( '/<iframe(.*?)src=/is', '<iframe$1src="' . $placeholder . '" data-lazy-src=', $iframe );

		$html = str_replace( $matches[0][ $k ], $iframe, $html );

		/**
		 * Filter the LazyLoad HTML output on iframes
		 *
		 * @since 2.6
		 *
		 * @param array $html Output that will be printed
		*/
		$html = apply_filters( 'rocket_lazyload_iframe_html', $html );
	}

	return $html;
}
add_filter( 'rocket_buffer', 'rocket_lazyload_iframes', PHP_INT_MAX );

/**
 * Check if we need to exclude LazyLoad on specific posts
 *
 * @since 2.5
 */
function rocket_deactivate_lazyload_on_specific_posts() {
	if ( is_rocket_post_excluded_option( 'lazyload' ) ) {
		add_filter( 'do_rocket_lazyload', '__return_false' );
	}

	if ( is_rocket_post_excluded_option( 'lazyload_iframes' ) ) {
		add_filter( 'do_rocket_lazyload_iframes', '__return_false' );
	}
}
add_action( 'wp', 'rocket_deactivate_lazyload_on_specific_posts' );

/**
 * Compatibility with images with srcset attribute
 *
 * @author Remy Perona
 *
 * @since 2.8 Also add sizes to the data-lazy-* attributes to prevent error in W3C validator
 * @since 2.7
 *
 * @param string $html HTML content.
 * @return string Modified HTML content
 */
function rocket_lazyload_on_srcset( $html ) {
	if ( preg_match( '/srcset=("(?:[^"]+)"|\'(?:[^\']+)\'|(?:[^ >]+))/i', $html ) ) {
		$html = str_replace( 'srcset=', 'data-lazy-srcset=', $html );
	}

	if ( preg_match( '/sizes=("(?:[^"]+)"|\'(?:[^\']+)\'|(?:[^ >]+))/i', $html ) ) {
		$html = str_replace( 'sizes=', 'data-lazy-sizes=', $html );
	}

	return $html;
}
add_filter( 'rocket_lazyload_html', 'rocket_lazyload_on_srcset' );
