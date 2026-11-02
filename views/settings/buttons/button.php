<?php
/**
 * Action button link template.
 *
 * @since 3.0
 *
 * @data array {
 *     Data to populate the template.
 *
 *     @type string $label      Button text.
 *     @type string $action     Action linked to the button.
 *     @type string $attributes String of attribute=value for the <button> tag, e.g. class, etc.
 *     @type array  $icon {
 *         Optional icon to display alongside the label.
 *
 *         @type string $data   Icon markup (e.g. an inline <span> or <svg>), empty string for no icon.
 *         @type bool   $before Whether to render the icon before the label instead of after.
 *     }
 *     @type string $tooltip    Tooltip text.
 * }
 */

defined( 'ABSPATH' ) || exit;

?>
<button id="wpr-action-<?php echo esc_attr( $data['action'] ); ?>" <?php echo $data['attributes']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $data['attributes'] escaped with sanitize_key & esc_attr ?>>
	
	<?php
	// Icon before or after the label, depending on $data['icon']['before'].
	$rocket_icon = ! empty( $data['icon']['data'] ) ? $data['icon']['data'] : '';

	echo $data['icon']['before'] ? $rocket_icon . $data['label'] : $data['label'] . $rocket_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.

	if ( ! empty( $data['tooltip'] ) ) :
		?>
		<div class="wpr-tooltip">
			<div class="wpr-tooltip-content">
				<?php echo esc_html( $data['tooltip'] ); ?>
			</div>
		</div>
	<?php endif; ?>
</button>
