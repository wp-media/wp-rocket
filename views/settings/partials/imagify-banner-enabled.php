<?php
/**
 * Imagify banner, displayed at the top of the Image & Media page once Imagify is enabled.
 *
 * The content is static placeholder content from the design.
 *
 * @since 3.23.6
 *
 * @param array $data {
 *     Banner data.
 *
 *     @type string $title Section title.
 * }
 */

defined( 'ABSPATH' ) || exit;

?>

<div class="wpr-optionHeader">
	<h3 class="wpr-title2"><?php echo esc_html( $data['title'] ); ?></h3>
</div>

<div class="wpr-imagifyBanner wpr-imagifyBanner--enabled">
	<div class="wpr-imagifyBanner-header">
		<div class="wpr-imagifyBanner-headerText">
			<p class="wpr-imagifyBanner-title wpr-imagifyBanner-title--valid"><?php esc_html_e( 'Imagify is installed and active', 'rocket' ); ?></p>
			<p class="wpr-imagifyBanner-description"><?php esc_html_e( 'New images are optimized automatically when you upload them.', 'rocket' ); ?></p>
		</div>
		<img class="wpr-imagifyBanner-logo" src="<?php echo esc_url( WP_ROCKET_ASSETS_IMG_URL . 'wpr-imagify-logo.svg' ); ?>" alt="Imagify" width="109" height="20">
	</div>

	<div class="wpr-imagifyBanner-content">
		<div class="wpr-imagifyBanner-list">
			<?php $this->render_part( 'imagify-banner-benefits' ); ?>
		</div>

		<div class="wpr-imagifyBanner-cta">
			<div class="wpr-imagifyBanner-ctaText">
				<p class="wpr-imagifyBanner-title"><?php esc_html_e( 'Next step: optimize your existing images', 'rocket' ); ?></p>
				<p><?php esc_html_e( 'Compress all your images in one click', 'rocket' ); ?></p>
			</div>
			<a class="wpr-button wpr-imagifyBanner-button wpr-imagifyBanner-button--arrow" href="<?php echo esc_url( admin_url( 'options-general.php?page=imagify' ) ); ?>"><?php esc_html_e( 'Open Imagify Settings', 'rocket' ); ?></a>
		</div>
	</div>
</div>
