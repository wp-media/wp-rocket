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
 * }
 */

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );
?>

<div class="wpr-field wpr-field--textarea <?php echo isset( $data['parent'] ) ? 'wpr-field--children' : ''; ?>">
	<?php echo $data['label']; ?>
	<div class="wpr-textarea">
		<textarea id="<?php echo esc_attr( $data['id'] ); ?>" name="wp_rocket_settings[<?php echo esc_attr( $data['id'] ); ?>]"><?php echo esc_textarea( $data['value'] ); ?></textarea>
	</div>
	<?php if ( ! empty( $data['description'] ) ) { ?>
		<div class="wpr-field-description">
			<?php echo $data['description']; ?>
		</div>
	<?php } ?>
</div>
