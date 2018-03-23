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

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );
?>

<div class="wpr-field wpr-field--radio <?php echo $data['container_class']; ?>">
	<div class="wpr-radio">
		<input type="checkbox" id="<?php echo esc_attr( $data['id'] ); ?>" class="" name="wp_rocket_settings[<?php echo esc_attr( $data['id'] ); ?>]" value="1" <?php checked( $data['value'], 1 ); ?>>
		<label for="<?php echo esc_attr( $data['id'] ); ?>" class="">
			<span data-l10n-active="<?php echo esc_attr_x( 'On', 'Active state of checkbox', 'rocket' ); ?>" 
  data-l10n-inactive="<?php echo esc_attr_x( 'Off', 'Inactive state of checkbox', 'rocket' ); ?>" class="wpr-radio-ui"></span>
			<?php echo esc_html( $data['label'] ); ?>
		</label>
	</div>

	<?php if ( ! empty( $data['description'] ) ) : ?>
		<div class="wpr-field-description">
			<?php echo $data['description']; ?>
		</div>
	<?php endif; ?>
</div>
