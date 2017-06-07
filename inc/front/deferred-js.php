<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

/**
 * Add defer attribute to script that should be deferred
 *
 * @since 2.10 Use defer attribute instead of labJS
 * @since 1.1.0
 *
 * @param string $buffer HTML content in the buffer.
 * @return string Updated HTML content
 */
function rocket_insert_deferred_js( $buffer ) {
	if ( get_rocket_option( 'defer_all_js' ) ) {
		return $buffer;
	}

	// Get all JS files with this regex.
	preg_match_all( '#<script.*src=[\'|"]([^\'|"]+\.js?.+)[\'|"].*></script>#iU', $buffer, $tags_match );

	if ( ! isset( $tags_match[0] ) ) {
		return $buffer;
	}

	foreach ( $tags_match[0] as $i => $tag ) {
		// Strip query args.
		$url = strtok( $tags_match[1][ $i ] , '?' );

		$deferred_js_files = array_flip( get_rocket_deferred_js_files() );

		// Check if this file should be deferred.
		if ( isset( $deferred_js_files[ $url ] ) ) {
			$deferred_tag = str_replace( '></script>', ' defer></script>', $tag );
			$buffer = str_replace( $tag, $deferred_tag, $buffer );
		}
	}

	return $buffer;
}
add_filter( 'rocket_buffer', 'rocket_insert_deferred_js', 11 );


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
	if ( ! get_rocket_option( 'defer_all_js' ) ) {
		return $buffer;
	}

	if ( is_rocket_post_excluded_option( 'defer_all_js' ) ) {
		return $buffer;
	}

	// Get all JS files with this regex.
	preg_match_all( '#<script(.*)src=[\'|"]([^\'|"]+\.js?.+)[\'|"](.*)></script>#iU', $buffer, $tags_match );

	if ( ! isset( $tags_match[0] ) ) {
		return $buffer;
	}

	$exclude_defer_js = array_flip( get_rocket_exclude_defer_js() );

	foreach ( $tags_match[0] as $i => $tag ) {
		// Strip query args.
		$path = parse_url( $tags_match[2][ $i ] , PHP_URL_PATH );

		// Check if this file should be deferred.
		if ( isset( $exclude_defer_js[ $path ] ) ) {
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

		$deferred_tag = str_replace( '></script>', ' defer></script>', $tag );
		$buffer = str_replace( $tag, $deferred_tag, $buffer );
	}

	return $buffer;
}
add_filter( 'rocket_buffer', 'rocket_defer_js', 14 );
