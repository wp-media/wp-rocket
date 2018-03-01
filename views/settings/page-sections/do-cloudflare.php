<?php
/**
 * Cloudflare section template.
 *
 * @since 3.0
 *
 * @param array {
 *     Section arguments.
 *
 *     @type string $id    Page section identifier.
 *     @type string $title Page section title.
 * }
 */

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );
?>

<div id="<?php echo esc_attr( $data['id'] ); ?>" class="wpr-page">
	<div class="wpr-sectionHeader">
		<h2 class="wpr-title1">
	        <img src="<?php echo WP_ROCKET_ASSETS_IMG_URL; ?>/logo-cloudflare.svg" width="153" height="51" alt="Logo WP Rocket">
	    </h2>
	</div>
	<?php $this->render_settings_sections( $data['id'] ); ?>
	<h3><?php esc_html_e( 'Cloudflare Cache', 'rocket' ); ?></h3>
	<p><?php esc_html_e( 'Purges cached resources for your website. Learn more', 'rocket' ); ?></p>
</div>
