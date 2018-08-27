<?php
/**
 * License section template.
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

<div class="wpr-sectionHeader">
	<h2 id="<?php echo esc_attr( $data['id'] ); ?>" class="wpr-title1 wpr-icon-important"><?php echo esc_html( $data['title'] ); ?></h2>
	<div class="wpr-sectionHeader-title wpr-title3">
		<?php _e( 'WP Rocket was not able to automatically validate your license.', 'rocket' ); ?>
	</div>
	<div class="wpr-sectionHeader-description">
		<?php
		// translators: %1$s = tutorial URL, %2$s = support URL.
		printf( __( 'Follow this <a href="%1$s" target="_blank">tutorial</a>, or contact <a href="%2$s" target="_blank">support</a> to get the engine started.', 'rocket' ), __( 'https://docs.wp-rocket.me/article/100-resolving-problems-with-license-validation/?utm_source=wp_plugin&utm_medium=wp_rocket', 'rocket' ), rocket_get_external_url( 'support', array(
			'utm_source' => 'wp_plugin',
			'utm_medium' => 'wp_rocket',
		) ) );
		?>
	</div>
</div><br>

<div class="wpr-fieldsContainer">
	<div class="wpr-fieldsContainer-fieldset wpr-fieldsContainer-fieldset--split">
		<?php $this->render_settings_sections( $data['id'] ); ?>
	</div>
</div>
