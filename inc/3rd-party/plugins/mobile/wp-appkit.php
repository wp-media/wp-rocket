<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

if ( class_exists( 'WpAppKit' ) ) :

add_filter( 'rocket_cache_reject_uri', 'rocket_add_appkit_exclude_pages' );

endif;

function rocket_add_appkit_exclude_pages( $urls ) {
    $urls[] = '/wp-appkit-api/(.*)';

    return $urls;
}

add_action( 'activate_wp-appkit/wp-appkit.php', 'rocket_activate_wp_appkit', 11 );
function rocket_activate_wp_appkit() {
    add_filter( 'rocket_cache_reject_uri', 'rocket_add_appkit_exclude_pages' );

    // Update the WP Rocket rules on the .htaccess
    flush_rocket_htaccess();

    // Regenerate the config file
    rocket_generate_config_file();
}

add_action( 'deactivate_wp-appkit/wp-appkit.php', 'rocket_deactivate_wp_appkit', 11 );
function rocket_deactivate_wp_appkit() {
    remove_filter( 'rocket_cache_reject_uri', 'rocket_add_appkit_exclude_pages' );

    // Update the WP Rocket rules on the .htaccess
    flush_rocket_htaccess();

    // Regenerate the config file
    rocket_generate_config_file();
}