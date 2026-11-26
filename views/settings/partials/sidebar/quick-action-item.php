<?php
/**
 * Dashboard sidebar quick action template.
 *
 * @since 3.24
 *
 * @param array $data {
 *     Quick action data.
 *
 *     @type string $id           Action identifier.
 *     @type string $title        Action title.
 *     @type string $description  Action description.
 *     @type string $button_label Button label.
 *     @type string $button_url   Button URL.
 *     @type bool   $disabled     Whether the button is disabled.
 * }
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wpr-quickAction">
	<h4 class="wpr-quickAction-title"><?php echo esc_html( $data['title'] ); ?></h4>
	<?php if ( '' !== $data['description'] ) : ?>
		<p class="wpr-quickAction-description"><?php echo esc_html( $data['description'] ); ?></p>
	<?php endif; ?>
	<div class="wpr-quickAction-actions">
		<?php if ( ! empty( $data['disabled'] ) ) : ?>
			<button type="button" class="wpr-quickAction-button" disabled><?php echo esc_html( $data['button_label'] ); ?></button>
		<?php else : ?>
			<a class="wpr-quickAction-button" href="<?php echo esc_url( $data['button_url'] ); ?>"><?php echo esc_html( $data['button_label'] ); ?></a>
		<?php endif; ?>
	</div>
</div>
