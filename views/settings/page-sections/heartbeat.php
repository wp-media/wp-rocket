<?php
/**
 * Heartbeat section template.
 *
 * @since  3.2
 * @author Grégory Viguier
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
			<img src="<?php echo esc_url( WP_ROCKET_ASSETS_IMG_URL . 'logo-heartbeat.svg' ); ?>" width="60" height="54" alt="Logo Heartbeat">
		</h2>
	</div>
	<?php $this->render_settings_sections( $data['id'] ); ?>
</div>
