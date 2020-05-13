<?php
/**
 * Action button link template.
 *
 * @since 3.0
 *
 * @data array {
 *     Data to populate the template.
 *
 *     @type string $label      Link text.
 *     @type string $action     Action linked to the button.
 *     @type string $attributes String of attribute=value for the <button> tag, e.g. class, etc.
 * }
 */

defined( 'ABSPATH' ) || exit;

?>
<button id="wpr-action-<?php echo esc_attr( $data['action'] ); ?>" <?php echo $data['attributes']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $data['attributes'] escaped with sanitize_key & esc_attr ?>><?php echo $data['label']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view. ?></button>
