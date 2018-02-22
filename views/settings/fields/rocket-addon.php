<?php
/**
 * One-click add-on block template.
 *
 * @since 3.0
 *
 * @param array $data {
 *     Checkbox Field arguments.
 *
 *     @type string $id          Field identifier.
 *     @type string $label       Add-on label.
 *     @type string $title       Add-on title.
 *     @type string $description Add-on description.
 *     @type string $logo        Add-on logo.
 *     @type string $value       Field value.
 * }
 */

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );
?>
<h4><?php echo esc_html( $data['label'] ); ?></h4>
<label for="<?php echo esc_attr( $data['id'] ); ?>" class=""><?php esc_html_e( 'Add-on status', 'rocket' ); ?></label> <input type="checkbox" id="<?php echo esc_attr( $data['id'] ); ?>" class="" name="wp_rocket_settings[<?php echo esc_attr( $data['id'] ); ?>]" value="1" <?php checked( $data['value'], 1 ); ?>>
<?php echo esc_attr( $data['logo'] ); ?>
<?php echo esc_html( $data['title'] ); ?>
<?php
if ( isset( $data['description'] ) ) {
	echo esc_html( $data['description'] );
}
?>
<a href="#<?php echo esc_attr( $data['id'] ); ?>"><?php esc_html_e( 'Modify options', 'rocket' ); ?></a>
