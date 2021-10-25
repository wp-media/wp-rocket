<?php
/**
 * Select field template.
 *
 * @since 3.10
 *
 * @param array $data {
 *     Radio buttons  arguments.
 *
 *     @type string $id              Field identifier.
 *     @type string $label           Field label.
 *     @type string $container_class Field container class.
 *     @type string $value           Field value.
 *     @type string $description     Field description.
 *     @type array  $options {
 *          Option options.
 *
 *          @type string $description Option value.
 *          @type string $label Option label.
 *          @type array  $sub_fields fields to show when option is selected.
 *     }
 * }
 */

defined( 'ABSPATH' ) || exit;

?>
<div id = '<?php echo esc_attr( $data['id'] ); ?>' class="wpr-field wpr-radio-buttons <?php echo esc_attr( $data['container_class'] ); ?>" data-default="<?php echo ( ! empty( $data['default'] ) ? 'wpr-radio-' . esc_attr( $data['default'] ) : '' ); ?>" <?php echo $data['parent']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $data['parent'] escaped with esc_attr. ?>>
	<div class="wpr-radio-buttons-container">
		<?php foreach ( $data['options'] as $value => $option ) : // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
			<button id ="wpr-radio-<?php echo esc_attr( $value ); ?>" class="wpr-button wpr-button--gray <?php echo ( $value === $data['value'] ? 'radio-active' : '' ); ?> <?php echo ( ! empty( $option['warning'] ) ? 'has-warning' : '' ); ?>"
					data-value="<?php echo esc_attr( $value ); ?>" <?php echo ( ! empty( $data['disabled'] ) ? esc_attr( $data['disabled'] ) : '' ); ?>>
				<?php echo esc_html( $option['label'] ); ?>
			</button>
		<?php endforeach; ?>
	</div>
	<?php foreach ( $data['options'] as $value => $option ) : // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
		<?php if ( ! empty( $option['warning'] ) ) : ?>
			<div class="wpr-fieldWarning wpr-radio-warning" data-parent="wpr-radio-<?php echo esc_attr( $value ); ?>">
				<div class="wpr-fieldWarning-title wpr-icon-important">
					<?php echo esc_html( $option['warning']['title'] ); ?>
				</div>
				<?php if ( isset( $option['warning']['description'] ) ) : ?>
					<div class="wpr-fieldWarning-description">
						<?php echo $option['warning']['description']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view. ?>
					</div>
				<?php endif; ?>
				<button class="wpr-button wpr-button--small wpr-button--icon wpr-icon-check"><?php echo esc_html( $option['warning']['button_label'] ); ?></button>
			</div>
		<?php endif; ?>

	<div class="wpr-extra-fields-container wpr-field--children no-space <?php echo ( $value === $data['value'] ? 'wpr-isOpen' : '' ); ?>" data-parent="wpr-radio-<?php echo esc_attr( $value ); ?>">

		<div class="wpr-field-description">
			<?php if ( ! empty( $option['description'] ) ) : ?>
				<div class="wpr-field-description ">
					<?php echo $option['description']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view. ?>
				</div>
			<?php endif; ?>
		</div>

		<?php
		do_action(
			'rocket_after_settings_radio_options',
			[
				'option_id'  => $value,
				'sub_fields' => $option['sub_fields'],
			]
		);
		?>

	</div>
	<?php endforeach; ?>
</div>

