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
 * @author Soponar Cristina
 */
function rocket_litespeed_header_purge_url( $url ) {
    $parse_url      = get_rocket_parse_url( $url );
    $path           = rtrim($parse_url['path'], '/');
    $private_prefix = 'X-LiteSpeed-Purge: ' . $path ;

    if ( headers_sent() ) {
        Logger::debug( 'X-LiteSpeed Headers already sent', [
            'headers_sent'
        ] );
        return;
    }

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
 * @author Soponar Cristina
 */
function rocket_litespeed_header_purge_all( ) {
    $private_prefix = 'X-LiteSpeed-Purge: *';

    if ( headers_sent() ) {
        return;
    }
    
    @header( $private_prefix ) ;
}