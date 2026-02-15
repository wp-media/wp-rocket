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
 *     @type string $tooltip    Tooltip text.
 * }
 */

defined( 'ABSPATH' ) || exit;

$data['url'] = ! empty( $data['url'] ) ? esc_url( $data['url'] ) : 'javascript:void(0);'; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
?>
<a href="<?php echo $data['url']; ?>" <?php echo $data['attributes']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $data['attributes'] escaped with sanitize_key & esc_attr ?>><?php echo $data['label']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view. ?>
	<?php if ( ! empty( $data['tooltip'] ) ) : ?>
		<div class="wpr-tooltip">
			<div class="wpr-tooltip-content">
				<?php echo esc_html( $data['tooltip'] ); ?>
			</div>
		</div>
	<?php endif; ?>
</a>
