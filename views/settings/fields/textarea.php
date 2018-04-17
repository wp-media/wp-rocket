<?php
/**
 * Textarea field template.
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
 *     @type string $helper      Field helper text.
 *     @type string $placeholder Field placeholder.
 * }
 */

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );
?>

<div class="wpr-field wpr-field--textarea <?php echo $data['container_class']; ?>"<?php echo $data['parent']; ?>>
	<?php if ( ! empty( $data['label'] ) ) : ?>
		<label for="<?php echo esc_attr( $data['id'] ); ?>" class="wpr-field-description-label"><?php echo $data['label']; ?></label>
	<?php endif; ?>
	<?php if ( ! empty( $data['description'] ) ) : ?>
		<div class="wpr-field-description">
			<?php echo $data['description']; ?>
		</div>
	<?php endif; ?>
	<div class="wpr-textarea">
		<textarea id="<?php echo esc_attr( $data['id'] ); ?>" name="wp_rocket_settings[<?php echo esc_attr( $data['id'] ); ?>]" placeholder="<?php echo esc_attr( $data['placeholder'] ); ?>"><?php echo esc_textarea( $data['value'] ); ?></textarea>
	</div>
	<?php if ( ! empty( $data['helper'] ) ) : ?>
		<div class="wpr-field-description wpr-field-description-helper">
			<?php echo $data['helper']; ?>
		</div>
	<?php endif; ?>
</div>
