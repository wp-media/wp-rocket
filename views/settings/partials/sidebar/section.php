<?php
/**
 * Dashboard sidebar section template: one white card of the sidebar column.
 *
 * @since 3.24
 *
 * @param array $data {
 *     Section data.
 *
 *     @type string $class   Optional extra CSS class of the section.
 *     @type string $content Section HTML content, already escaped by its own templates.
 * }
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $data['content'] ) ) {
	return;
}
?>
<div class="<?php echo esc_attr( trim( 'wpr-Dashboard-sidebar-section ' . ( $data['class'] ?? '' ) ) ); ?>">
	<?php echo $data['content']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in its own view. ?>
</div>
