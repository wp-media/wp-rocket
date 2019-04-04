<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Defer all JS files.
 *
 * @since 2.10
 * @author Remy Perona
 *
 * @param string $buffer HTML content.
 * @return string Updated HTML content
 */
function rocket_defer_js( $buffer ) {
	if ( ( defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) || ( defined( 'DONOTASYNCCSS' ) && DONOTASYNCCSS ) ) {
		return;
	}

	if ( ! get_rocket_option( 'defer_all_js' ) ) {
		return $buffer;
	}

	if ( is_rocket_post_excluded_option( 'defer_all_js' ) ) {
		return $buffer;
	}

	$buffer_nocomments = preg_replace( '/<!--(.*)-->/Uis', '', $buffer );
	// Get all JS files with this regex.
	preg_match_all( '#<script\s+([^>]+[\s\'"])?src\s*=\s*[\'"]\s*?([^\'"]+\.js(?:\?[^\'"]*)?)\s*?[\'"]([^>]+)?\/?>#iU', $buffer_nocomments, $tags_match );

	if ( ! isset( $tags_match[0] ) ) {
		return $buffer;
	}

	$exclude_defer_js = implode( '|', get_rocket_exclude_defer_js() );

	foreach ( $tags_match[0] as $i => $tag ) {
		// Check if this file should be deferred.
		if ( preg_match( '#(' . $exclude_defer_js . ')#i', $tags_match[2][ $i ] ) ) {
			continue;
		}

		// Don't add defer if already async.
		if ( false !== strpos( $tags_match[1][ $i ], 'async' ) || false !== strpos( $tags_match[3][ $i ], 'async' ) ) {
			continue;
		}

		// Don't add defer if already defer.
		if ( false !== strpos( $tags_match[1][ $i ], 'defer' ) || false !== strpos( $tags_match[3][ $i ], 'defer' ) ) {
			continue;
		}

		$deferred_tag = str_replace( '>', ' defer>', $tag );
		$buffer       = str_replace( $tag, $deferred_tag, $buffer );
	}

	return $buffer;
}
add_filter( 'rocket_buffer', 'rocket_defer_js', 15 );
