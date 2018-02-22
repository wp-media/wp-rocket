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
<h2 id="<?php echo esc_attr( $data['id'] ); ?>"><?php echo esc_html( $data['title'] ); ?></h2>
<?php _e( 'WP Rocket was not able to automatically validate your license.', 'rocket' ); ?>
<?php
// translators: %1$s = tutorial URL, %2$s = support URL.
printf( __( 'Follow this <a href="%1$s">tutorial</a>, or contact <a href="%2$s">support</a> to get the engine started.', 'rocket' ), '', '' );
?>
<?php $this->render_settings_sections( $data['id'] ); ?>
