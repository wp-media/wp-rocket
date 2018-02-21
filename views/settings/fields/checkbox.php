<?php
/**
 * Checkbox template.
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
 *     @type array  $warning {
 *         Warning panel content.
 *
 *         @type string $title        Warning title.
 *         @type string $description  Warning description.
 *         @type string $button_label Warning Button label.
 *     }
 * }
 */

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );
?>
<input type="checkbox" id="<?php echo esc_attr( $data['id'] ); ?>" class="" name="wp_rocket_settings[<?php echo esc_attr( $data['id'] ); ?>]" value="1" <?php checked( $data['value'], 1 ); ?><?php echo isset( $data['input_attr'] ) ? $data['input_attr'] : ''; ?>> <label for="<?php echo esc_attr( $data['id'] ); ?>" class=""><?php echo $data['label']; ?></label>
<?php
if ( isset( $data['description'] ) ) {
	echo esc_html( $data['description'] );
}
?>
<?php if ( isset( $data['warning'] ) ) { ?>
<div>
<?php echo esc_html( $data['warning']['title'] ); ?> <?php echo esc_html( $data['warning']['description'] ); ?>
<button><?php echo esc_html( $data['warning']['button_label'] ); ?></button>
<?php
}
