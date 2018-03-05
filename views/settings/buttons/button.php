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

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );
?>
<button id="wpr-action-<?php echo esc_attr( $data['action'] ); ?>" <?php echo $data['attributes']; ?>><?php echo esc_html( $data['label'] ); ?></button>
