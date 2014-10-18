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
add_action( 'wp_head', 'rocket_lazyload_script', PHP_INT_MAX );
function rocket_lazyload_script() {
	if ( ! apply_filters( 'do_rocket_lazyload', true ) ) {
		return;
	}

	echo '<script type="text/javascript">(function(a,e){function f(){var d=0;if(e.body&&e.body.offsetWidth){d=e.body.offsetHeight}if(e.compatMode=="CSS1Compat"&&e.documentElement&&e.documentElement.offsetWidth){d=e.documentElement.offsetHeight}if(a.innerWidth&&a.innerHeight){d=a.innerHeight}return d}function b(g){var d=ot=0;if(g.offsetParent){do{d+=g.offsetLeft;ot+=g.offsetTop}while(g=g.offsetParent)}return{left:d,top:ot}}function c(){var l=e.querySelectorAll("[data-lazy-original]");var j=a.pageYOffset||e.documentElement.scrollTop||e.body.scrollTop;var d=f();for(var k=0;k<l.length;k++){var h=l[k];var g=b(h).top;if(g<(d+j)){h.src=h.getAttribute("data-lazy-original");h.removeAttribute("data-lazy-original")}}}if(a.addEventListener){a.addEventListener("DOMContentLoaded",c,false);a.addEventListener("scroll",c,false)}else{a.attachEvent("onload",c);a.attachEvent("onscroll",c)}})(window,document);</script>';
}

/**
 * Replace Gravatar, thumbnails, images in post content and in widget text by LazyLoad
 *
 * @since 2.2 Better regex pattern in a replace_callback
 * @since 1.3.5 It's possible to exclude LazyLoad process by used do_rocket_lazyload filter
 * @since 1.2.0 It's possible to not lazy load an image with data-no-lazy attribute
 * @since 1.1.0 Don't lazy-load if the thumbnail has already been run through previously
 * @since 1.0.1 Add priority of hooks at maximum later with PHP_INT_MAX
 * @since 1.0
 */
add_filter( 'get_avatar', 'rocket_lazyload_images', PHP_INT_MAX );
add_filter( 'the_content', 'rocket_lazyload_images', PHP_INT_MAX );
add_filter( 'widget_text', 'rocket_lazyload_images', PHP_INT_MAX );
add_filter( 'post_thumbnail_html', 'rocket_lazyload_images', PHP_INT_MAX );
function rocket_lazyload_images( $html ) {
	// Don't LazyLoad if the thumbnail is in a feed or in a post preview
	if ( is_feed() || is_preview() || empty( $html ) || ( defined( 'DONOTLAZYLOAD' ) && DONOTLAZYLOAD ) ) {
		return $html;
	}

	// Don't LazyLoad if the thumbnail has already been run through previously or stop process with a hook
	if ( ! apply_filters( 'do_rocket_lazyload', true ) ) {
		return $html;
	}

	$html = preg_replace_callback( '#<img([^>]*) src=("(?:[^"]+)"|\'(?:[^\']+)\'|(?:[^ >]+))([^>]*)>#', '__rocket_lazyload_replace_callback', $html );

	return $html;
}


/**
 * Used to check if we have to LazyLoad this or not
 *
 * @since 2.2
 *
 */

function __rocket_lazyload_replace_callback( $matches ) {

	if ( strpos( $matches[1] . $matches[3], 'data-no-lazy=' ) === false && strpos( $matches[1] . $matches[3], 'data-lazy-original=' ) === false ) {
		return sprintf( '<img%1$s src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==" data-lazy-original=%2$s%3$s><noscript><img%1$s src=%2$s%3$s></noscript>',
						$matches[1], $matches[2], $matches[3] );
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
remove_filter( 'the_content', 'convert_smilies' );
remove_filter( 'the_excerpt', 'convert_smilies' );
remove_filter( 'comment_text', 'convert_smilies' );

add_filter( 'the_content', 'rocket_convert_smilies' );
add_filter( 'the_excerpt', 'rocket_convert_smilies' );
add_filter( 'comment_text', 'rocket_convert_smilies' );

/**
 * Convert text equivalent of smilies to images.
 *
 * @source convert_smilies() in /wp-includes/formattings.php
 * @since 2.0
 */
function rocket_convert_smilies( $text ) {
	global $wp_smiliessearch;

	$output = '';
	if ( get_option( 'use_smilies' ) && ! empty( $wp_smiliessearch ) ) {
		// HTML loop taken from texturize function, could possible be consolidated
		$textarr = preg_split( '/(<.*>)/U', $text, -1, PREG_SPLIT_DELIM_CAPTURE ); // capture the tags as well as in between
		$stop = count( $textarr );// loop stuff

		// Ignore proessing of specific tags
		$tags_to_ignore = 'code|pre|style|script|textarea';
		$ignore_block_element = '';

		for ( $i = 0; $i < $stop; $i++ ) {
			$content = $textarr[ $i ];

			// If we're in an ignore block, wait until we find its closing tag
			if ( '' == $ignore_block_element && preg_match( '/^<(' . $tags_to_ignore . ')>/', $content, $matches ) )  {
				$ignore_block_element = $matches[1];
			}

			// If it's not a tag and not in ignore block
			if ( '' ==  $ignore_block_element && strlen( $content ) > 0 && '<' != $content[0] ) {
				$content = preg_replace_callback( $wp_smiliessearch, 'rocket_translate_smiley', $content );
			}

			// did we exit ignore block
			if ( '' != $ignore_block_element && '</' . $ignore_block_element . '>' == $content )  {
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
 */
function rocket_translate_smiley( $matches ) {
	
	global $wpsmiliestrans;

	if ( ! count( $matches ) ) {
		return '';
	}

	$smiley = trim( reset( $matches ) );
	$img = $wpsmiliestrans[ $smiley ];

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

	// Don't lazy-load if process is stopped with a hook
	 if ( apply_filters( 'do_rocket_lazyload', true ) && ( ! defined( 'DONOTLAZYLOAD' ) || ! DONOTLAZYLOAD ) ) {

		return sprintf( ' <img src="data:image/gif;base64,R0lGODdhAQABAPAAAP///wAAACwAAAAAAQABAEACAkQBADs=" data-lazy-original="%s" alt="%s" class="wp-smiley" /> ', esc_url( $src_url ), esc_attr( $smiley ) );

	} else {

		return sprintf( ' <img src="%s" alt="%s" class="wp-smiley" /> ', esc_url( $src_url ), esc_attr( $smiley ) );

	}

}