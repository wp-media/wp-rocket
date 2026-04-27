<?php
/**
 * Fair use policy notice partial.
 *
 * @since 3.22
 *
 * @param array $data {
 *     Notice data.
 *
 *     @type string $title       Notice title.
 *     @type string $description Notice description text.
 *     @type string $link_url    URL for the action link.
 *     @type string $link_text   Text for the action link.
 * }
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="wpr-cdn-fair-use">
	<div class="wpr-cdn-fair-use__content">
		<p class="wpr-cdn-fair-use__title"><?php echo esc_html( $data['title'] ); ?></p>
		<p class="wpr-cdn-fair-use__description"><?php echo esc_html( $data['description'] ); ?></p>
	</div>
	<?php if ( ! empty( $data['link_url'] ) ) : ?>
	<div class="wpr-cdn-fair-use__action">
		<a href="<?php echo esc_url( $data['link_url'] ); ?>" class="wpr-cdn-fair-use__link" target="_blank" rel="noopener noreferrer">
			<?php echo esc_html( $data['link_text'] ); ?>
		</a>
	</div>
	<?php endif; ?>
</div>
