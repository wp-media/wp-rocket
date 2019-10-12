<?php
use WP_Rocket\Logger\Logger;

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Purge Litespeed URL
 *
 * @since 3.4.1
 * @author Soponar Cristina
 *
 * @param  string $url The URL to purge.
 * @return void
 *
 */
function rocket_litespeed_header_purge_url( $url ) {
    if ( headers_sent() ) {
        Logger::debug( 'X-LiteSpeed Headers already sent', [
            'headers_sent'
        ] );
        return;
    }

    $parse_url      = get_rocket_parse_url( $url );
    $path           = rtrim($parse_url['path'], '/');
    $private_prefix = 'X-LiteSpeed-Purge: ' . $path ;

    Logger::debug( 'X-LiteSpeed', [
        'rocket_litespeed_header_purge_url',
        'path' => $private_prefix,
    ] );

    @header( $private_prefix ) ;
}

/**
 * Purge Litespeed Cache
 *
 * @since 3.4.1
 * @author Soponar Cristina
 *
 * @param  string $url The URL to purge.
 * @return void
 *
 */
function rocket_litespeed_header_purge_all( ) {
    if ( headers_sent() ) {
        return;
    }
    $private_prefix = 'X-LiteSpeed-Purge: *';
    @header( $private_prefix ) ;
}