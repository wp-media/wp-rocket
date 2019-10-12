<?php
use WP_Rocket\Logger\Logger;

defined('ABSPATH') || die('Cheatin&#8217; uh?');

/**
 * Allow to purge the Litespeed cache
 *
 * @since  3.4.1
 * @author Soponar Cristina
 */
if ( isset( $_SERVER['HTTP_X_LSCACHE'] ) && $_SERVER['HTTP_X_LSCACHE'] ) {
    /**
     * Purge all the domain
     *
     * @since  3.4.1
     * @author Soponar Cristina
     *
     * @param string $root The path of home cache file.
     * @param string $lang The current lang to purge.
     * @param string $url  The home url.
     */
    function rocket_litespeed_clean_domain($root, $lang, $url)
    {
        Logger::info('X-LiteSpeed', [
            'rocket_litespeed_clean_domain'
        ]);
        rocket_litespeed_header_purge_url( trailingslashit( $url ) );
    }
    add_action('before_rocket_clean_domain', 'rocket_litespeed_clean_domain', 10, 3);

    /**
     * Purge a specific page
     *
     * @since  3.4.1
     * @author Soponar Cristina
     *
     * @param string $url The url to purge.
     */
    function rocket_litespeed_clean_file($url)
    {
        Logger::info('X-LiteSpeed', [
            'rocket_litespeed_clean_file'
        ]);
        rocket_litespeed_header_purge_url( trailingslashit( $url ) );
    }
    add_action('before_rocket_clean_file', 'rocket_litespeed_clean_file');

    /**
     * Purge the homepage and its pagination
     *
     * @since  3.4.1
     * @author Soponar Cristina
     *
     * @param string $root The path of home cache file.
     * @param string $lang The current lang to purge.
     */
    function rocket_litespeed_clean_home($root, $lang)
    {
        $home_url            = trailingslashit( get_rocket_i18n_home_url( $lang ) );
        $home_pagination_url = $home_url . trailingslashit( $GLOBALS['wp_rewrite']->pagination_base );

        Logger::info('X-LiteSpeed', [
            'rocket_litespeed_clean_home'
        ]);
        rocket_litespeed_header_purge_url( $home_url );
        rocket_litespeed_header_purge_url( $home_pagination_url );
    }
    add_action('before_rocket_clean_home', 'rocket_litespeed_clean_home', 10, 2);
}
