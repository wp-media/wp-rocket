<?php
/**
 * Select field template.
 *
 * @since 3.0
 *
 * @param array $data {
 *     Checkbox Field arguments.
 *
 *     @type string $id          Field identifier.
 *     @type string $label       Field label.
 *     @type string $value       Field value.
 *     @type string $description Field description.
 *     @type array  $choices {
 *          Option choices.
 *
 *          @type string $value Option value.
 *          @type string $label Option label.
 *     }
 * }
 */

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );
?>
<select id="<?php echo esc_attr( $data['id'] ); ?>" name="wp_rocket_settings[<?php echo esc_attr( $data['id'] ); ?>]">
<?php foreach ( $data['choices'] as $value => $label ) { ?>
	<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $data['value'] ); ?>><?php echo esc_html( $label ); ?></option>
<?php } ?>
</select>
