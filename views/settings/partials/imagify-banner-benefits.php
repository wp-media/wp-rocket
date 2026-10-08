<?php
/**
 * Imagify banner benefits list.
 *
 * Static placeholder content from the design.
 *
 * @since 3.23
 */

defined( 'ABSPATH' ) || exit;

$rocket_benefits = [
	[
		'image' => 'wpr-imagify-benefit-1.png',
		// Translators: %1$s = <strong>, %2$s = </strong>.
		'text'  => sprintf( esc_html__( '%1$sCompress all your images%2$s in one click', 'rocket' ), '<strong>', '</strong>' ),
	],
	[
		'image' => 'wpr-imagify-benefit-2.png',
		// Translators: %1$s = <strong>, %2$s = </strong>.
		'text'  => sprintf( esc_html__( '%1$sConvert images%2$s to WebP and Avif', 'rocket' ), '<strong>', '</strong>' ),
	],
	[
		'image' => 'wpr-imagify-benefit-3.png',
		// Translators: %1$s = <strong>, %2$s = </strong>.
		'text'  => sprintf( esc_html__( '%1$sResize your image%2$s on the fly', 'rocket' ), '<strong>', '</strong>' ),
	],
	[
		'image' => 'wpr-imagify-benefit-4.png',
		// Translators: %1$s = <strong>, %2$s = </strong>.
		'text'  => sprintf( esc_html__( '%1$sFree plan includes 20MB/month%2$s (around 200 images)', 'rocket' ), '<strong>', '</strong>' ),
	],
];
?>
<ul class="wpr-imagifyBanner-benefits">
	<?php foreach ( $rocket_benefits as $rocket_benefit ) : ?>
		<li class="wpr-imagifyBanner-benefit">
			<img src="<?php echo esc_url( WP_ROCKET_ASSETS_IMG_URL . $rocket_benefit['image'] ); ?>" alt="" width="42" height="32">
			<span><?php echo wp_kses( $rocket_benefit['text'], [ 'strong' => [] ] ); ?></span>
		</li>
	<?php endforeach; ?>
</ul>
