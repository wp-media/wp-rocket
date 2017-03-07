<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

/**
 * Conflict with WP Touch: deactivate LazyLoad on mobile theme
 *
 * @since 2.1
 */
function rocket_deactivate_lazyload_with_wptouch() {
	if ( ( function_exists( 'wptouch_is_mobile_theme_showing' ) && wptouch_is_mobile_theme_showing() ) || ( function_exists( 'bnc_wptouch_is_mobile' ) && bnc_wptouch_is_mobile() ) ) {
		add_filter( 'do_rocket_lazyload', '__return_false' );
	}
}
add_action( 'init', 'rocket_deactivate_lazyload_with_wptouch' );

/**
 * Conflict with LayerSlider: don't add width and height attributes on all images
 *
 * @since 2.1
 */
function rocket_deactivate_specify_image_dimensions_with_layerslider() {
	remove_filter( 'rocket_buffer', 'rocket_specify_image_dimensions' );
}
add_action( 'layerslider_ready', 'rocket_deactivate_specify_image_dimensions_with_layerslider' );

/**
 * Conflict with AppBanners: don't minify inline script when HTML minification is activated
 *
 * @since 2.2.4
 *
 * @param array $html_options An array of WP Rocket options.
 * @return array Array without the inline js minify option
 */
function rocket_deactivate_js_minifier_with_appbanner( $html_options ) {
	if ( isset( $html_options['jsMinifier'] ) && class_exists( 'AppBanners' ) ) {
	 	unset( $html_options['jsMinifier'] );
	}
	 return $html_options;
}
add_filter( 'rocket_minify_html_options', 'rocket_deactivate_js_minifier_with_appbanner' );

/**
 * Conflict with Envira Gallery: don't apply LazyLoad on all images
 *
 * @since 2.3.10
 *
 * @param string $attr Envira gallery image attributes.
 * @return string Updated attributes
 */
function rocket_deactivate_lazyload_on_envira_gallery( $attr ) {
	return $attr . ' data-no-lazy="1" ';
}
add_filter( 'envira_gallery_output_image_attr', 'rocket_deactivate_lazyload_on_envira_gallery', PHP_INT_MAX );

/**
 * Conflict with Envira Gallery: don't apply LazyLoad on all images
 *
 * @since 2.3.10
 *
 * @param string $images Envira gallery images HTML code.
 * @return string Updated HTML code
 */
function rocket_deactivate_lazyload_on_envira_gallery_indexable_images( $images ) {
	$images = str_replace( '<img' , '<img data-no-lazy="1" ', $images );

	return $images;
}
add_filter( 'envira_gallery_indexable_images', 'rocket_deactivate_lazyload_on_envira_gallery_indexable_images', PHP_INT_MAX );

/**
 * Conflict with Envira Gallery: changes the URL argument if using WP Rocket CDN and Envira
 *
 * @since 2.6.5
 *
 * @param array $args An array of arguments.
 * @return array Updated array of arguments
 */
function rocket_cdn_resize_image_args_on_envira_gallery( $args ) {
	if ( ! isset( $args['url'] ) || (int) get_rocket_option( 'cdn' ) === 0 ) {
		return $args;
	}

	$cnames_host = get_rocket_cnames_host();
	$url_host    = parse_url( $args['url'], PHP_URL_HOST );
	$home_host   = parse_url( home_url(), PHP_URL_HOST );

	if ( in_array( $url_host, $cnames_host, true ) ) {
		$args['url'] = str_replace( $url_host, $home_host , $args['url'] );
	}

	return $args;
}
add_filter( 'envira_gallery_resize_image_args', 'rocket_cdn_resize_image_args_on_envira_gallery' );

/**
 * Conflict with Envira Gallery: changes the resized URL if using WP Rocket CDN and Envira
 *
 * @since 2.6.5
 *
 * @param string $url Resized image URL.
 * @return string Resized image URL using the CDN URL
 */
function rocket_cdn_resized_url_on_envira_gallery( $url ) {
	if ( (int) get_rocket_option( 'cdn' ) === 0 ) {
		return $url;
	}

	$url = get_rocket_cdn_url( $url, array( 'all', 'images' ) );
	return $url;
}
add_filter( 'envira_gallery_resize_image_resized_url', 'rocket_cdn_resized_url_on_envira_gallery' );

/**
 * Conflict with Meta Slider (Nivo Slider): don't apply LazyLoad on all images
 *
 * @since 2.4
 *
 * @param array $slide Slide attributes.
 * @return array Updated slide attributes
 */
function rocket_deactivate_lazyload_on_metaslider( $slide ) {
	$slide['data-no-lazy'] = 1;
	return $slide;
}
add_filter( 'metaslider_nivo_slider_image_attributes', 'rocket_deactivate_lazyload_on_metaslider' );

/**
 * Conflict with Soliloquy: don't apply LazyLoad on all images
 *
 * @since 2.4.2
 *
 * @param string $attr Image attributes.
 * @return string Updated attributes
 */
function rocket_deactivate_lazyload_on_soliloquy( $attr ) {
	return $attr . ' data-no-lazy="1" ';
}
add_filter( 'soliloquy_output_image_attr', 'rocket_deactivate_lazyload_on_soliloquy', PHP_INT_MAX );

/**
 * Conflict with Soliloquy: don't apply LazyLoad on all images
 *
 * @since 2.4.2
 *
 * @param string $images Image HTML code.
 * @return string Updated image HTML code
 */
function rocket_deactivate_lazyload_on_soliloquy_indexable_images( $images ) {
	$images = str_replace( '<img' , '<img data-no-lazy="1" ', $images );

	return $images;
}
add_filter( 'soliloquy_indexable_images', 'rocket_deactivate_lazyload_on_soliloquy_indexable_images', PHP_INT_MAX );

/**
 * Conflict with Thrive Leads: override the DONOTCACHEPAGE behavior because this plugin add this constant!
 *
 * @since 2.5
 */
function rocket_override_donotcachepage_on_thrive_leads() {
	return defined( 'TVE_LEADS_VERSION' ) && TVE_LEADS_VERSION > 0;
}
add_filter( 'rocket_override_donotcachepage', 'rocket_override_donotcachepage_on_thrive_leads' );

/**
 * Conflict with Aqua Resizer & IrishMiss Framework: Apply CDN without blank src!!
 *
 * @since 2.5.8 Add compatibility with IrishMiss Framework
 * @since 2.5.5
 */
function rocket_cdn_on_aqua_resizer() {
	if ( function_exists( 'aq_resize' ) || function_exists( 'miss_display_image' ) ) {
		remove_filter( 'wp_get_attachment_url' , 'rocket_cdn_file', PHP_INT_MAX );
		add_filter( 'rocket_lazyload_html', 'rocket_add_cdn_on_custom_attr' );
	}
}
add_action( 'init', 'rocket_cdn_on_aqua_resizer' );

/**
 * Conflict with Revolution Slider & Master Slider: Apply CDN on data-lazyload|data-src attribute.
 *
 * @since 2.5.5
 */
function rocket_cdn_on_sliders_with_lazyload() {
	if ( class_exists( 'RevSliderFront' ) || class_exists( 'Master_Slider' ) ) {
		add_filter( 'rocket_cdn_images_html', 'rocket_add_cdn_on_custom_attr' );
	}
}
add_action( 'init', 'rocket_cdn_on_sliders_with_lazyload' );

/**
 * Conflict with MailChimp List Subscribe Form: Enqueue style without lack of performance, grrrr!!!
 *
 * @since 2.6
 */
function rocket_fix_mailchimp_main_css() {
	if ( ! defined( 'MCSF_VER' ) || ! function_exists( 'mailchimpSF_main_css' ) ) {
		return;
	}

	$blog_id    = get_current_blog_id();
	$cache_path = WP_ROCKET_MINIFY_CACHE_PATH . $blog_id . '/';
	$cache_url  = WP_ROCKET_MINIFY_CACHE_URL . $blog_id . '/';
	$css_path   = $cache_path . 'mailchimpSF_main_css.css';

	if ( ! is_dir( $cache_path ) ) {
		rocket_mkdir_p( $cache_path );
	}

	if ( ! file_exists( $css_path ) ) {
		ob_start();
		mailchimpSF_main_css();
		$content = ob_get_contents();
		ob_end_clean();

		rocket_put_content( $css_path, $content );
	}

	wp_deregister_style( 'mailchimpSF_main_css' );
	wp_register_style( 'mailchimpSF_main_css', $cache_url . 'mailchimpSF_main_css.css', null, MCSF_VER );
}
