<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

if ( ! function_exists( 'tve_editor_url' ) ) {
    return;
}
/**
 * Forces Thrive Visual Editor’s bot detection to assume a human visitor.
 *
 * @since 2.8.11
 * @author Remy Perona
 *
 * @param  boolean|integer $bot_detection 1|0 when crawler has|not been detected, FALSE when user agent string is unavailable
 * @return integer
 */
add_filter( 'tve_dash_is_crawler', '__rocket_tve_dash_is_crawler', PHP_INT_MAX );
function __rocket_tve_dash_is_crawler( $bot_detection ) {
    return 0;
}
