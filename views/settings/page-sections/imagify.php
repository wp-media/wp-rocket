<?php
/**
 * Imagify section template.
 *
 * @since 3.2
 */

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );
?>

<div id="imagify" class="wpr-Page">
	<div class="wpr-sectionHeader">
		<h2 class="wpr-title1 wpr-icon-imagify-hover"><?php esc_html_e( 'Image Optimization', 'rocket' ); ?></h2>
	</div>
	<div class="wpr-imagify">
		<div class="wpr-imagify-description">
			<p><?php _e( '<strong>WP ROCKET</strong> created <strong>IMAGIFY</strong> <strong>for best-in-class image optimization.</strong>', 'rocket' ); ?></p>
			<p><?php esc_html_e( 'Compress image to make your website faster, all while maintaining image quality.', 'rocket' ); ?></p>
			<p class="wpr-imagify-more"><?php esc_html_e( 'More on Imagify:', 'rocket' ); ?></p>
			<ul>
				<li><a href="https://wordpress.org/plugins/imagify/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Imagify Plugin Page', 'rocket' ); ?></a></li>
				<li><a href="https://imagify.io/?utm_source=wp-rocket&utm_campaign=plugin_partner&utm_medium=partnership" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Imagify Website', 'rocket' ); ?></a></li>
				<li><a href="https://www.imagely.com/image-optimization-plugin-comparison/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Review of Image Compression Plugins', 'rocket' ); ?></a></li>
			</ul>
			<?php
			if ( ! \Imagify_Partner::is_imagify_activated() ) {
				$imagify = new \Imagify_Partner( 8 );

				if ( \Imagify_Partner::is_imagify_installed() ) {
					$button_text = __( 'Activate Imagify', 'rocket' );
				} else {
					$button_text = __( 'Install Imagify', 'rocket' );
				}

				echo '<a class="button-primary" href="' . esc_url( $imagify->get_post_install_url() ) . '">' . esc_html( $button_text ) . '</a>';
			}
			?>
		</div>
		<div class="wpr-imagify-screenshot">
			<img src="<?php echo esc_url( WP_ROCKET_ASSETS_IMG_URL . 'imagify.jpg' ); ?>" alt="" width="613" height="394">
		</div>
	</div>
</div>
