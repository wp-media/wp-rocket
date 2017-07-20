<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

add_action( 'acf/save_post', 'rocket_clear_cache_on_acf_options_save' );
function rocket_clear_cache_on_acf_options_save( $post_id ) {
    if ( 'options' === $post_id ) {
        rocket_clean_domain();
    }
}
