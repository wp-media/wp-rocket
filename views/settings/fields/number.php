<?php
/**
 * Number field template.
 *
 * @since 3.0
 *
 * @param array $data {
 *     Checkbox Field arguments.
 *
 *     @type string $id              Field identifier.
 *     @type string $label           Field label.
 *     @type string $container_class Field container class.
 *     @type string $value           Field value.
 *     @type string $description     Field description.
 * }
 */

defined( 'ABSPATH' ) || exit;

?>

<div class="wpr-field wpr-field--text wpr-field--number <?php echo esc_attr( $data['container_class'] ); ?>">
	<div class="wpr-text wpr-text--number">
		<label for="<?php echo esc_attr( $data['id'] ); ?>"><?php echo $data['label']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view. ?></label>
		<input type="number" id="<?php echo esc_attr( $data['id'] ); ?>" name="wp_rocket_settings[<?php echo esc_attr( $data['id'] ); ?>]" value="<?php echo esc_attr( $data['value'] ); ?>">
	</div>

	<?php if ( ! empty( $data['description'] ) ) : ?>
	<div class="wpr-field-description">
		<?php echo $data['description']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view. ?>
	</div>
	<?php endif; ?>
</div>
