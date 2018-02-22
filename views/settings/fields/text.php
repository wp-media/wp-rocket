<?php
/**
 * Text template.
 *
 * @since 3.0
 *
 * @param array $data {
 *     Checkbox Field arguments.
 *
 *     @type string $id          Field identifier.
 *     @type string $parent      Parent field identifier.
 *     @type string $label       Field label.
 *     @type string $value       Field value.
 *     @type string $description Field description.
 *     @type string $input_attr  Attributes for the input field.
 * }
 */

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );
?>
<label for="<?php echo esc_attr( $data['id'] ); ?>" class=""><?php echo $data['label']; ?></label>
<input type="text" id="<?php echo esc_attr( $data['id'] ); ?>" class="" name="wp_rocket_settings[<?php echo esc_attr( $data['id'] ); ?>]" value="<?php echo esc_attr( $data['value'] ); ?>"<?php echo isset( $data['input_attr'] ) ? $data['input_attr'] : ''; ?>>

