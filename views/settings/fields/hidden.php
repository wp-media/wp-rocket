<?php
/**
 * Hidden field template.
 *
 * @since 3.0
 *
 * @param array $data {
 *     Field arguments.
 *
 *     @type string $id    Field indentifier.
 *     @type mixed  $value Field value.
 * }
 */

defined( 'ABSPATH' ) || exit;

if ( is_array( $data['value'] ) ) {
	foreach ( $data['value'] as $rocket_value ) {
		?>
		<input type="hidden" name="wp_rocket_settings[<?php echo esc_attr( $data['id'] ); ?>[]]" value="<?php echo esc_attr( $rocket_value ); ?>">
		<?php
	}
} else {
	?>
	<input type="hidden" id="<?php echo esc_attr( $data['id'] ); ?>" name="wp_rocket_settings[<?php echo esc_attr( $data['id'] ); ?>]" value="<?php echo esc_attr( $data['value'] ); ?>">
	<?php
}
