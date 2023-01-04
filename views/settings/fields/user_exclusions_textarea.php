<?php
/**
 * User Exclusion Textarea field template.
 *
 * @since 3.13
 *
 * @param array $data {
 *     User Exclusion Textarea Field arguments.
 *
 *     @type string $id              Field identifier.
 *     @type string $container_class Field Container class.
 *     @type string $label           Field label.
 *     @type string $value           Field value.
 *     @type string $description     Field description.
 *     @type string $helper          Field helper text.
 *     @type string $placeholder     Field placeholder.
 * }
 */

defined( 'ABSPATH' ) || exit;

?>

<div class="wpr-field wpr-field--userexclusiontextarea <?php echo esc_attr( $data['container_class'] ); ?>"<?php echo $data['parent'] ?? ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $data['parent'] escaped with esc_attr. ?>>
<div class="wpr-list open">
	<div class="wpr-list-header">
		<h3><?php echo esc_html( $data['label'] ); ?></h3>
	</div>
	<div class="wpr-list-body">
		<textarea name="wp_rocket_settings[<?php echo esc_attr( $data['id'] ); ?>]" placeholder="<?php echo esc_attr( $data['placeholder'] ); ?>"><?php echo esc_textarea( $data['value'] ); ?></textarea>
	</div>
	<?php if ( ! empty( $data['helper'] ) ) : ?>
		<div class="wpr-field-description wpr-field-description-helper">
			<?php echo $data['helper']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view. ?>
		</div>
	<?php endif; ?>
	<?php
	/**
	 * Fires after the display of a textarea field on WP Rocket settings page
	 *
	 * The dynamic portion of the name corresponds to the field ID
	 *
	 * @since 3.13
	 */
	do_action( 'rocket_after_textarea_field_' . $data['id'] );
	?>
</div>
