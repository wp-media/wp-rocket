<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

if ( defined( 'SCCSS_FILE' ) ) :
    add_action( 'wp_enqueue_scripts', 'rocket_cache_sccss', 1 );
    add_action( 'update_option_sccss_settings', 'rocket_delete_sccss_cache_file' );
    add_filter( 'rocket_cache_busting_filename', 'rocket_sccss_cache_busting_filename' );
endif;

function rocket_cache_sccss() {
    $sccss = rocket_sccss_get_paths();

    if ( ! file_exists( $sccss['filepath'] ) ) {
    	rocket_sccss_create_cache_file( $sccss['bustingpath'], $sccss['filepath'] );
    }

    wp_enqueue_style(  'scss', $sccss['url'] );
    remove_action( 'wp_enqueue_scripts', 'sccss_register_style', 99 );
}

function rocket_delete_sccss_cache_file() {
    $sccss = rocket_sccss_get_paths();

    @unlink( $sccss['filepath'] );
    rocket_sccss_create_cache_file(  $sccss['bustingpath'], $sccss['filepath'] );
}

function rocket_sccss_cache_busting_filename( $filename ) {
    if ( false !== strpos( $filename, 'sccss' ) ) {
        return 'sccss.css';        
    }

    return $filename;
}

function rocket_sccss_get_paths() {
    $blog_id              = get_current_blog_id();
    $cache_busting_path   = WP_ROCKET_CACHE_BUSTING_PATH . $blog_id . '/';
    $cache_sccss_filepath = $cache_busting_path . 'sccss.css';
    $cache_sccss_url      = WP_ROCKET_CACHE_BUSTING_URL . $blog_id . '/sccss.css';

    return array( 'busingpath' => $cache_busting_path, 'filepath' => $cache_sccss_filepath, 'url' => $cache_sccss_url );
}

function rocket_sccss_create_cache_file( $cache_busting_path, $cache_sccss_filepath ) {
    $options     = get_option( 'sccss_settings' );
    $raw_content = isset( $options['sccss-content'] ) ? $options['sccss-content'] : '';
    $content     = wp_kses( $raw_content, array( '\'', '\"' ) );
    $content     = str_replace( '&gt;', '>', $content );

    if ( ! is_dir( $cache_busting_path ) ) {
        rocket_mkdir_p( $cache_busting_path );
    }

    rocket_put_content( $cache_sccss_filepath, $content );
}