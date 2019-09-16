<?php
/**
 * Varnish Cache section template.
 *
 * @since  3.5
 * @author Remy Perona
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

<div id="<?php echo esc_attr( $data['id'] ); ?>" class="wpr-Page">
	<div class="wpr-sectionHeader">
		<h2 class="wpr-title1">
			<img src="<?php echo esc_url( WP_ROCKET_ASSETS_IMG_URL . 'logo-varnish.svg' ); ?>" width="48" height="42" alt="" class="wpr-sectionHeader-logo"><?php echo esc_html( $data['title'] ); ?>
		</h2>
	</div>
	<?php $this->render_settings_sections( $data['id'] ); ?>
</div>
