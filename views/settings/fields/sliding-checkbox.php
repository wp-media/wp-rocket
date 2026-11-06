<?php
/**
 * Sliding checkbox template.
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

defined( 'ABSPATH' ) || exit;

?>

<div class="wpr-field wpr-field--radio <?php echo esc_attr( $data['container_class'] ); ?>">
	<div class="wpr-radio">
		<input type="checkbox" id="<?php echo esc_attr( $data['id'] ); ?>" class="" name="wp_rocket_settings[<?php echo esc_attr( $data['id'] ); ?>]" value="1" <?php checked( $data['value'], 1 ); ?>>
		<label for="<?php echo esc_attr( $data['id'] ); ?>" class="">
			<span data-l10n-active="On"
				data-l10n-inactive="Off" class="wpr-radio-ui"></span>
			<?php echo esc_html( $data['label'] ); ?>
		</label>
	</div>

	<?php if ( ! empty( $data['description'] ) ) : ?>
		<div class="wpr-field-description">
			<?php echo $data['description']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view. ?>
		</div>
	<?php endif; ?>
</div>
