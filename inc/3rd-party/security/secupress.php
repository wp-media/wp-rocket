<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

if ( defined( 'SECUPRESS_VERSION' ) ) :

    add_filter( 'rocket_cache_reject_uri', '__rocket_exclude_secupress_move_login' );
    add_action( 'update_option_secupress_users-login_settings', '__rocket_after_update_single_options', 10, 2 );

endif;

function __rocket_exclude_secupress_move_login( $urls ) {
    if ( get_option( 'secupress_active_submodule_move-login' ) ) {
        $secupress_move_login = get_option( 'secupress_users-login_settings' );
        $urls[] = $secupress_move_login['move-login_slug-login'];
        $urls[] = $secupress_move_login['move-login_slug-logout'];
        $urls[] = $secupress_move_login['move-login_slug-register'];
        $urls[] = $secupress_move_login['move-login_slug-lostpassword'];
        $urls[] = $secupress_move_login['move-login_slug-resetpass'];
    }

    return $urls;
}

add_action( 'activate_secupress/secupress.php', '__rocket_activate_secupress', 11 );
function __rocket_activate_secupress() {
    add_filter( 'rocket_cache_reject_uri', '__rocket_exclude_secupress_move_login' );

    // Update the WP Rocket rules on the .htaccess
    flush_rocket_htaccess();

    // Regenerate the config file
    rocket_generate_config_file();
}

add_action( 'deactivate_secupress/secupress.php', '__rocket_deactivate_secupress', 11 );
function __rocket_deactivate_secupress() {
    remove_filter( 'rocket_cache_reject_uri', '__rocket_exclude_secupress_move_login' );

    // Update the WP Rocket rules on the .htaccess
    flush_rocket_htaccess();

    // Regenerate the config file
    rocket_generate_config_file();
}