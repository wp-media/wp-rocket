<?php
/**
 * Text template.
 *
 * @since 3.0
 *
 * @param array $data {
 *     Checkbox Field arguments.
 *
 *     @type string $id              Field identifier.
 *     @type string $parent          Parent field identifier.
 *     @type string $label           Field label.
 *     @type string $container_class Field container class.
 *     @type string $value           Field value.
 *     @type string $description     Field description.
 *     @type string $input_attr      Attributes for the input field.
 * }
 */

defined( 'ABSPATH' ) || exit;

?>

<div class="wpr-field wpr-field--text <?php echo esc_attr( $data['container_class'] ); ?>">
	<div class="wpr-text">
		<?php if ( ! empty( $data['description'] ) ) : ?>
		<div class="wpr-flex">
			<label for="<?php echo esc_attr( $data['id'] ); ?>"><?php echo esc_html( $data['label'] ); ?></label>
			<div class="wpr-field-description">
				<?php echo $data['description']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view. ?>
			</div>
		</div>
		<?php else : ?>
			<label for="<?php echo esc_attr( $data['id'] ); ?>"><?php echo esc_html( $data['label'] ); ?></label>
		<?php endif; ?>
		<input type="text" id="<?php echo esc_attr( $data['id'] ); ?>" class="" name="wp_rocket_settings[<?php echo esc_attr( $data['id'] ); ?>]" value="<?php echo esc_attr( $data['value'] ); ?>"<?php echo $data['input_attr']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view. ?>>
		<?php if ( ! empty( $data['helper'] ) ) : ?>
		<div class="wpr-field-description wpr-field-description-helper">
			<?php echo $data['helper']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view. ?>
		</div>
		<?php endif; ?>
	</div>
</div>
