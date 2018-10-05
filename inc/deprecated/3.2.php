<?php

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Add a message about Imagify on the "Upload New Media" screen and WP Rocket options page.
 *
 * @since 2.7
 * @deprecated 3.2
 */
function rocket_imagify_notice() {
	_deprecated_function( __FUNCTION__, '3.2' );
	$current_screen = get_current_screen();

	// Add the notice only on the "WP Rocket" settings, "Media Library" & "Upload New Media" screens.
	if ( 'admin_notices' === current_filter() && ( isset( $current_screen ) && 'settings_page_wprocket' !== $current_screen->base ) ) {
		return;
	}

	$boxes = get_user_meta( $GLOBALS['current_user']->ID, 'rocket_boxes', true );

	if ( defined( 'IMAGIFY_VERSION' ) || in_array( __FUNCTION__, (array) $boxes, true ) || 1 === get_option( 'wp_rocket_dismiss_imagify_notice' ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$imagify_plugin       = 'imagify/imagify.php';
	$is_imagify_installed = rocket_is_plugin_installed( $imagify_plugin );

	$action_url = $is_imagify_installed ?
	rocket_get_plugin_activation_link( $imagify_plugin )
		:
	wp_nonce_url( add_query_arg(
		array(
			'action' => 'install-plugin',
			'plugin' => 'imagify',
		),
		admin_url( 'update.php' )
	), 'install-plugin_imagify' );

	$details_url = add_query_arg(
		array(
			'tab'       => 'plugin-information',
			'plugin'    => 'imagify',
			'TB_iframe' => true,
			'width'     => 722,
			'height'    => 949,
		),
		admin_url( 'plugin-install.php' )
	);

	$classes = $is_imagify_installed ? '' : ' install-now';
	$cta_txt = $is_imagify_installed ? esc_html__( 'Activate Imagify', 'rocket' ) : esc_html__( 'Install Imagify for Free', 'rocket' );

	$dismiss_url = wp_nonce_url(
		admin_url( 'admin-post.php?action=rocket_ignore&box=' . __FUNCTION__ ),
		'rocket_ignore_' . __FUNCTION__
	);
	?>

	<div id="plugin-filter" class="updated plugin-card plugin-card-imagify rkt-imagify-notice">
		<a href="<?php echo $dismiss_url; ?>" class="rkt-cross"><span class="dashicons dashicons-no"></span></a>

		<p class="rkt-imagify-logo">
			<img src="<?php echo WP_ROCKET_ASSETS_IMG_URL; ?>logo-imagify.png" srcset="<?php echo WP_ROCKET_ASSETS_IMG_URL; ?>logo-imagify.svg 2x" alt="Imagify" width="150" height="18">
		</p>
		<p class="rkt-imagify-msg">
			<?php _e( 'Speed up your website and boost your SEO by reducing image file sizes without losing quality with Imagify.', 'rocket' ); ?>
		</p>
		<p class="rkt-imagify-cta">
			<a data-slug="imagify" href="<?php echo $action_url; ?>" class="button button-primary<?php echo $classes; ?>"><?php echo $cta_txt; ?></a>
			<?php if ( ! $is_imagify_installed ) : ?>
			<br><a data-slug="imagify" data-name="Imagify Image Optimizer" class="thickbox open-plugin-details-modal" href="<?php echo $details_url; ?>"><?php _e( 'More details', 'rocket' ); ?></a>
			<?php endif; ?>
		</p>
	</div>

	<?php
}
