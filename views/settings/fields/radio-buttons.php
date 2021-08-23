<?php
/**
 * Select field template.
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
 *     @type array  $choices {
 *          Option choices.
 *
 *          @type string $value Option value.
 *          @type string $label Option label.
 *     }
 * }
 */

defined( 'ABSPATH' ) || exit;

?>
<div id = '<?php echo esc_attr( $data['id'] ); ?>' class="wpr-field wpr-radio-buttons <?php echo esc_attr( $data['container_class'] ); ?>"<?php echo $data['parent']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $data['parent'] escaped with esc_attr. ?>>
	<div class="wpr-radio-buttons-container">
		<?php foreach ( $data['options'] as $value => $option ) : // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
			<button id ="wpr-radio-<?php echo esc_attr( $value ); ?>" class="wpr-button wpr-button--gray <?php echo ( $value === $data['value'] ? 'radio-active' : '' ); ?>"
					data-value="<?php echo esc_attr( $value ); ?>" <?php echo ( ! empty( $data['disabled'] ) ? esc_attr( $data['disabled'] ) : '' ); ?>>
				<?php echo esc_html( $option['label'] ); ?>
			</button>
		<?php endforeach; ?>
	</div>
	<?php foreach ( $data['options'] as $value => $option ) : // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>

	<div class="wpr-extra-fields-container wpr-field--children no-space <?php echo ( $value === $data['value'] ? 'wpr-isOpen' : '' ); ?>" data-parent="wpr-radio-<?php echo esc_attr( $value ); ?>">

		<div class="wpr-field-description">
			<?php if ( ! empty( $option['description'] ) ) : ?>
				<div class="wpr-field-description ">
					<?php echo $option['description']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view. ?>
				</div>
			<?php endif; ?>
		</div>

		<?php do_action( 'rocket_after_settings_radio_options', $value, $option['sub_fields'] ); ?>

	</div>
	<?php endforeach; ?>
</div>

