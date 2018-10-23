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
			<p>
			<?php
			// Translators: %1$s = <strong>, %2$s = </strong>, %3$s = <span class="imagify-name">, %4$s = </span>.
			printf( __( '%1$sWP ROCKET%2$s created %3$sIMAGIFY%4$s %1$sfor best-in-class image optimization.%2$s', 'rocket' ), '<strong>', '</strong>', '<span class="wpr-imagify-name">', '</span>' );
			?>
			</p>
			<p><?php esc_html_e( 'Compress image to make your website faster, all while maintaining image quality.', 'rocket' ); ?></p>
			<p class="wpr-imagify-more"><?php esc_html_e( 'More on Imagify:', 'rocket' ); ?></p>
			<ul>
				<li><a href="https://wordpress.org/plugins/imagify/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Imagify Plugin Page', 'rocket' ); ?></a></li>
				<li><a href="https://imagify.io/?utm_source=wp-rocket&utm_campaign=plugin_partner&utm_medium=partnership" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Imagify Website', 'rocket' ); ?></a></li>
				<li><a href="https://www.imagely.com/image-optimization-plugin-comparison/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Review of Image Compression Plugins', 'rocket' ); ?></a></li>
			</ul>
			<?php
			if ( ! \Imagify_Partner::is_imagify_activated() ) {
				$imagify = new \Imagify_Partner( 'wp-rocket' );

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
			<img src="https://wp-rocket.me/wp-content/uploads/1/imagify.jpg?ver=<?php echo esc_attr( time() ); ?>" alt="" width="613" height="394">
		</div>
	</div>
</div>
