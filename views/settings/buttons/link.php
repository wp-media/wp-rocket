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
 *     @type string $url        URL for the href attribute.
 *     @type string $attributes String of attribute=value for the <a> tag, e.g. class, target, etc.
 * }
 */

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );
?>
<a href="<?php echo esc_url( $data['url'] ); ?>" <?php echo $data['attributes']; ?>><?php echo esc_html( $data['label'] ); ?></a>
