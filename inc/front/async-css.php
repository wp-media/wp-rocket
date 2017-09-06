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

	if ( is_rocket_post_excluded_option( 'async_css' ) ) {
		return $buffer;
	}

	$excluded_css = array_flip( get_rocket_exclude_async_css() );

	// Get all css files with this regex.
	preg_match_all( apply_filters( 'rocket_async_css_regex_pattern', '/(?=<link[^>]*\s(rel\s*=\s*[\'"]stylesheet["\']))<link[^>]*\shref\s*=\s*[\'"]([^\'"]+)[\'"](.*)>/iU' ), $buffer, $tags_match );

	if ( ! isset( $tags_match[0] ) ) {
		return $buffer;
	}

	$noscripts = '';

	foreach ( $tags_match[0] as $i => $tag ) {
		// Strip query args.
		$path = parse_url( $tags_match[2][ $i ] , PHP_URL_PATH );

		// Check if this file should be deferred.
		if ( isset( $excluded_css[ $path ] ) ) {
			continue;
		}

	    $preload = str_replace( 'stylesheet', 'preload', $tags_match[1][ $i ] );
	    $onload  = str_replace( $tags_match[3][ $i ], ' as="style" onload=""' . $tags_match[3][ $i ] . '>', $tags_match[3][ $i ] );
	    $tag	 = str_replace( $tags_match[3][ $i ] . '>', $onload, $tag );
	    $tag	 = str_replace( $tags_match[1][ $i ], $preload, $tag );
	    $tag 	 = str_replace( 'onload=""', 'onload="this.rel=\'stylesheet\'"', $tag );
	    $buffer  = str_replace( $tags_match[0][ $i ], $tag, $buffer );

		$noscripts .= '<noscript>' . $tags_match[0][ $i ] . '</noscript>';
	}

	$buffer = str_replace( '</body>', $noscripts . '</body>', $buffer );	

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

	if ( is_rocket_post_excluded_option( 'async_css' ) ) {
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

	if ( defined( 'DONOTASYNCCSS' ) && DONOTASYNCCSS ) {
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

	$critical_css = wp_kses( get_rocket_option( 'critical_css' ), array( '\'', '\"' ) );
	$critical_css = str_replace( '&gt;', '>', $critical_css );

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

	if ( is_rocket_post_excluded_option( 'async_css' ) ) {
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

	if ( defined( 'DONOTASYNCCSS' ) && DONOTASYNCCSS ) {
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
<script>
/*! loadCSS. [c]2017 Filament Group, Inc. MIT License */
!function(a){"use strict";var b=function(b,c,d){function e(a){return h.body?a():void setTimeout(function(){e(a)})}function f(){i.addEventListener&&i.removeEventListener("load",f),i.media=d||"all"}var g,h=a.document,i=h.createElement("link");if(c)g=c;else{var j=(h.body||h.getElementsByTagName("head")[0]).childNodes;g=j[j.length-1]}var k=h.styleSheets;i.rel="stylesheet",i.href=b,i.media="only x",e(function(){g.parentNode.insertBefore(i,c?g:g.nextSibling)});var l=function(a){for(var b=i.href,c=k.length;c--;)if(k[c].href===b)return a();setTimeout(function(){l(a)})};return i.addEventListener&&i.addEventListener("load",f),i.onloadcssdefined=l,l(f),i};"undefined"!=typeof exports?exports.loadCSS=b:a.loadCSS=b}("undefined"!=typeof global?global:this);
/*! loadCSS rel=preload polyfill. [c]2017 Filament Group, Inc. MIT License */
!function(a){if(a.loadCSS){var b=loadCSS.relpreload={};if(b.support=function(){try{return a.document.createElement("link").relList.supports("preload")}catch(b){return!1}},b.poly=function(){for(var b=a.document.getElementsByTagName("link"),c=0;c<b.length;c++){var d=b[c];"preload"===d.rel&&"style"===d.getAttribute("as")&&(a.loadCSS(d.href,d,d.getAttribute("media")),d.rel=null)}},!b.support()){b.poly();var c=a.setInterval(b.poly,300);a.addEventListener&&a.addEventListener("load",function(){b.poly(),a.clearInterval(c)}),a.attachEvent&&a.attachEvent("onload",function(){a.clearInterval(c)})}}}(this);
</script>
JS;
}
add_action( 'wp_head', 'rocket_insert_load_css', PHP_INT_MAX );
