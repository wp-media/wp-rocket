<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

/**
 * Conflict with WP Touch: deactivate LazyLoad on mobile theme
 *
 * @since 2.1
 */
add_action( 'init', '__deactivate_rocket_lazyload_with_wptouch' );
function __deactivate_rocket_lazyload_with_wptouch()
{
    if ( ( function_exists( 'wptouch_is_mobile_theme_showing' ) && wptouch_is_mobile_theme_showing() ) || ( function_exists( 'bnc_wptouch_is_mobile' ) && bnc_wptouch_is_mobile() ) )
    {
		add_filter( 'do_rocket_lazyload', '__return_false' );
    }
}

/**
 * Conflict with LayerSlider: don't add width and height attributes on all images
 *
 * @since 2.1
 */
add_action( 'layerslider_ready', '__deactivate_rocket_specify_image_dimensions_with_layerslider' );
function __deactivate_rocket_specify_image_dimensions_with_layerslider()
{
	remove_filter( 'rocket_buffer', 'rocket_specify_image_dimensions' );
}

/**
 * Conflict with AppBanners: don't minify inline script when HTML minification is activated
 *
 * @since 2.2.4
 */
add_filter( 'rocket_minify_html_options', '__deactivate_rocket_jsMinifier_with_appbanner' );
function __deactivate_rocket_jsMinifier_with_appbanner( $html_options ) {
	 if ( isset( $html_options['jsMinifier'] ) && class_exists( 'AppBanners' ) ) {
	 	unset( $html_options['jsMinifier'] );
	 }
	 return $html_options;
}

/**
 * Conflict with Envira Gallery: don't apply LazyLoad on all images
 *
 * @since 2.3.10
 */
add_filter( 'envira_gallery_output_image_attr', '__deactivate_rocket_lazyload_on_envira_gallery', PHP_INT_MAX );
function __deactivate_rocket_lazyload_on_envira_gallery( $attr ) {
	return $attr . ' data-no-lazy="1" ';
}

add_filter( 'envira_gallery_indexable_images', '__deactivate_rocket_lazyload_on_envira_gallery_indexable_images', PHP_INT_MAX );
function __deactivate_rocket_lazyload_on_envira_gallery_indexable_images( $images ) {
	$images = str_replace( '<img' , '<img data-no-lazy="1" ', $images );
	
	return $images;
}

/**
 * Conflict with Meta Slider (Nivo Slider): don't apply LazyLoad on all images
 *
 * @since 2.4
 */
add_filter( 'metaslider_nivo_slider_image_attributes', '__deactivate_rocket_lazyload_on_metaslider' );
function __deactivate_rocket_lazyload_on_metaslider( $slide ) {
	$slide['data-no-lazy'] = 1;
	return $slide;
}

/**
 * Conflict with Soliloquy: don't apply LazyLoad on all images
 *
 * @since 2.4.2
 */
add_filter( 'soliloquy_output_image_attr', '__deactivate_rocket_lazyload_on_soliloquy', PHP_INT_MAX );
function __deactivate_rocket_lazyload_on_soliloquy( $attr ) {
	return $attr . ' data-no-lazy="1" ';
}

add_filter( 'soliloquy_indexable_images', '__deactivate_rocket_lazyload_on_soliloquy_indexable_images', PHP_INT_MAX );
function __deactivate_rocket_lazyload_on_soliloquy_indexable_images( $images ) {
	$images = str_replace( '<img' , '<img data-no-lazy="1" ', $images );
	
	return $images;
}

/**
 * Conflict with Thrive Leads: override the DONOTCACHEPAGE behavior because this plugin add this constant!
 *
 * @since 2.5
 */
add_filter( 'rocket_override_donotcachepage', '__override_rocket_donotcachepage_on_thrive_leads' );
function __override_rocket_donotcachepage_on_thrive_leads() {
	return defined( 'TVE_LEADS_VERSION' ) && TVE_LEADS_VERSION > 0;
}

/**
 * Conflict with KK Star Rating: Clear the cache when a post gets rated.
 *
 * @since 2.5.3
 */
add_action( 'kksr_rate', '__rocket_clear_cache_on_kksr_rate' );
function __rocket_clear_cache_on_kksr_rate( $post_id ) {
	rocket_clean_post( $post_id );	
}

/**
 * Conflict with Aqua Resizer: Apply CDN without blank src!!
 *
 * @since 2.5.5
 */
add_action( 'init', '__rocket_cdn_on_aqua_resizer' );
function __rocket_cdn_on_aqua_resizer() {
	if( function_exists( 'aq_resize' ) ) {
		remove_filter( 'wp_get_attachment_url' , 'rocket_cdn_file', PHP_INT_MAX );
		add_filter( 'rocket_lazyload_html', 'rocket_add_cdn_on_custom_attr' );
	}
}

/**
 * Conflict with Revolution Slider & Master Slider: Apply CDN on data-lazyload|data-src attribute.
 *
 * @since 2.5.5
 */
add_action( 'init', '__rocket_cdn_on_sliders_with_lazyload' );
function __rocket_cdn_on_sliders_with_lazyload() {
	if( class_exists( 'RevSliderFront' ) || class_exists( 'Master_Slider' ) ) {
		add_filter( 'rocket_cdn_images_html', 'rocket_add_cdn_on_custom_attr' );
	}
}

/**
 * Conflict with WP Retina x2: Apply CDN on srcset attribute.
 *
 * @since 2.5.5
 */
add_filter( 'wr2x_img_retina_url', '__rocket_cdn_on_images_from_wp_retina_x2' );
add_filter( 'wr2x_img_url', '__rocket_cdn_on_images_from_wp_retina_x2' );
function __rocket_cdn_on_images_from_wp_retina_x2( $url ) {
	if ( wr2x_is_pro() ) {
		$cdn_domain = wr2x_getoption( "cdn_domain", "wr2x_advanced", "" );
	}
	if ( empty( $cdn_domain ) ) {
		return get_rocket_cdn_url( $url, array( 'all', 'images' ) );
	}
	
	return $url;
}