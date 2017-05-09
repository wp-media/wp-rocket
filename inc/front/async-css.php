<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

/**
 * Defer loading of CSS files
 *
 * @since 2.10
 * @author Remy Perona
 *
 * @param string $buffer HTML code.
 * @return string Updated HTML code
 */
function rocket_async_css( $buffer ) {
	if ( ! get_rocket_option( 'async_css' ) ) {
		return $buffer;
	}

	$excluded_css = array_flip( get_rocket_exclude_async_css() );

	// Get all css files with this regex.
	preg_match_all( apply_filters( 'rocket_async_css_regex_pattern', '/<link\s*.+href=[\'|"]([^\'|"]+\.css?.+)[\'|"](.+)>/iU' ), $buffer, $tags_match );

	if ( ! isset( $tags_match[0] ) ) {
		return $buffer;
	}

	foreach ( $tags_match[0] as $i => $tag ) {
		// Strip query args.
		$path = parse_url( $tags_match[1][ $i ] , PHP_URL_PATH );

		// Check if this file should be deferred.
		if ( isset( $excluded_css[ $path ] ) ) {
			continue;
		}

		$tag = '<noscript class="async-styles">' . $tags_match[0][ $i ] . '</noscript>';
		$buffer = str_replace( $tags_match[0][ $i ], $tag, $buffer );
	}
	return $buffer;
}
add_filter( 'rocket_buffer', 'rocket_async_css', 15 );


/**
 * Insert critical CSS in the <head>
 *
 * @since 2.10
 * @author Remy Perona
 */
function rocket_insert_critical_css() {
	global $pagenow;

	if ( ! get_rocket_option( 'async_css' ) ) {
		return;
	}

	// Don't apply on wp-login.php/wp-register.php.
	if ( in_array( $pagenow, array( 'wp-login.php', 'wp-register.php' ), true ) ) {
		return;
	}

	// Don't apply if DONOTCACHEPAGE is defined.
	if ( defined( 'DONOTCACHEPAGE' ) && DONOTCACHEPAGE ) {
		return;
	}

	// Don't apply if user is logged-in and cache for logged-in user is off.
	if ( is_user_logged_in() && ! get_rocket_option( 'cache_logged_user' ) ) {
		return;
	}

	// This filter is documented in inc/front/process.php.
	$rocket_cache_search = apply_filters( 'rocket_cache_search', false );

	// Don't apply on search page.
	if ( is_search() && ! $rocket_cache_search ) {
		return;
	}

	// Don't apply on excluded pages.
	if ( in_array( $_SERVER['REQUEST_URI'] , get_rocket_option( 'cache_reject_uri' , array() ), true ) ) {
		return;
	}

	// Don't apply on 404 page.
	if ( is_404() ) {
		return;
	}

	$critical_css = wp_filter_nohtml_kses( get_rocket_option( 'critical_css' ) );

	echo '<style id="rocket-critical-css">' . $critical_css . '</style>';
}
add_action( 'wp_head', 'rocket_insert_critical_css', 1 );

/**
 * Insert loadCSS script in <head>
 *
 * @since 2.10
 * @author Remy Perona
 */
function rocket_insert_load_css() {
	global $pagenow;

	if ( ! get_rocket_option( 'async_css' ) ) {
		return;
	}

	// Don't apply on wp-login.php/wp-register.php.
	if ( in_array( $pagenow, array( 'wp-login.php', 'wp-register.php' ), true ) ) {
		return;
	}

	// Don't apply if DONOTCACHEPAGE is defined.
	if ( defined( 'DONOTCACHEPAGE' ) && DONOTCACHEPAGE ) {
		return;
	}

	// Don't apply if user is logged-in and cache for logged-in user is off.
	if ( is_user_logged_in() && ! get_rocket_option( 'cache_logged_user' ) ) {
		return;
	}

	// This filter is documented in inc/front/process.php.
	$rocket_cache_search = apply_filters( 'rocket_cache_search', false );

	// Don't apply on search page.
	if ( is_search() && ! $rocket_cache_search ) {
		return;
	}

	// Don't apply on excluded pages.
	if ( in_array( $_SERVER['REQUEST_URI'] , get_rocket_option( 'cache_reject_uri' , array() ), true ) ) {
		return;
	}

	// Don't apply on 404 page.
	if ( is_404() ) {
		return;
	}

	echo <<<JS
<script data-no-minify="1" data-cf-async="false">
var loadDeferredStyles = function() {
    var addStylesNode = document.getElementsByClassName("async-styles");
    for ( var i = addStylesNode.length; i--; ) {
    	var replacement = document.createElement("div");
		replacement.innerHTML = addStylesNode[i].textContent;
		document.body.appendChild(replacement)
		addStylesNode[i].parentElement.removeChild(addStylesNode[i]);
	}
};
var raf = requestAnimationFrame || mozRequestAnimationFrame ||
	webkitRequestAnimationFrame || msRequestAnimationFrame;
if (raf) raf(function() { window.setTimeout(loadDeferredStyles, 0); });
else window.addEventListener('load', loadDeferredStyles);
</script>
JS;
}
add_action( 'wp_head', 'rocket_insert_load_css', PHP_INT_MAX );
