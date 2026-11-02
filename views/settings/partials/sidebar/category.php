<?php
/**
 * Dashboard sidebar category template.
 *
 * @since 3.24
 *
 * @param array $data {
 *     Category data.
 *
 *     @type string $title   Category title.
 *     @type string $content Category HTML content, already escaped by its own template.
 * }
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $data['content'] ) ) {
	return;
}
?>
<div class="wpr-Dashboard-sidebar-category">
	<h3 class="wpr-title2 wpr-Dashboard-sidebar-title"><?php echo esc_html( $data['title'] ); ?></h3>
	<?php echo $data['content']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in its own view. ?>
</div>
