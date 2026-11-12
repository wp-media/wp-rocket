<?php
/**
 * Imagify banner benefits list.
 *
 * Static placeholder content from the design, shared with the Imagify card of the Dashboard sidebar through
 * Render::get_imagify_benefits().
 *
 * @since 3.24
 */

defined( 'ABSPATH' ) || exit;

$rocket_benefits = $this->get_imagify_benefits();
?>
<ul class="wpr-imagifyBanner-benefits">
	<?php foreach ( $rocket_benefits as $rocket_benefit ) : ?>
		<li class="wpr-imagifyBanner-benefit">
			<img src="<?php echo esc_url( WP_ROCKET_ASSETS_IMG_URL . $rocket_benefit['image'] ); ?>" alt="" width="42" height="32">
			<span><?php echo wp_kses( $rocket_benefit['text'], [ 'strong' => [] ] ); ?></span>
		</li>
	<?php endforeach; ?>
</ul>
