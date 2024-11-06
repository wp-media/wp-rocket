<?php
/**
 * Hidden field template.
 *
 * @since 3.0
 *
 * @param array $data {
 *     Field arguments.
 *
 *     @type string $id    Field identifier.
 *     @type mixed  $value Field value.
 * }
 */

defined( 'ABSPATH' ) || exit;

?>
<input type="hidden" id="<?php echo esc_attr( $data['id'] ); ?>" name="wp_rocket_settings[<?php echo esc_attr( $data['id'] ); ?>]" value="<?php echo esc_attr( $data['value'] ); ?>">
