<?php
/**
 * Imagify banner, displayed at the top of the Image & Media page while Imagify is not enabled.
 *
 * The content is static placeholder content from the design.
 *
 * @since 3.24
 *
 * @param array $data {
 *     Banner data.
 *
 *     @type string $title Section title.
 * }
 */

defined( 'ABSPATH' ) || exit;

$rocket_button_text = \Imagify_Partner::is_imagify_installed() ? __( 'Activate', 'rocket' ) : __( 'Install', 'rocket' );
$rocket_button_url  = ( new \Imagify_Partner( 'wp-rocket' ) )->get_post_install_url();
?>

<div class="wpr-optionHeader">
	<h3 class="wpr-title2"><?php echo esc_html( $data['title'] ); ?></h3>
</div>

<div class="wpr-imagifyBanner" role="region" aria-labelledby="wpr-imagifyBanner-title">
	<div class="wpr-imagifyBanner-header">
		<div class="wpr-imagifyBanner-headerText">
			<p class="wpr-imagifyBanner-title" id="wpr-imagifyBanner-title"><?php esc_html_e( 'Give your website an extra speed boost!', 'rocket' ); ?></p>
			<p class="wpr-imagifyBanner-description"><?php esc_html_e( 'Images can account for 50% of your loading time.', 'rocket' ); ?></p>
		</div>
		<img class="wpr-imagifyBanner-logo" src="<?php echo esc_url( WP_ROCKET_ASSETS_IMG_URL . 'wpr-imagify-logo.svg' ); ?>" alt="" width="109" height="20">
	</div>

	<div class="wpr-imagifyBanner-content">
		<div class="wpr-imagifyBanner-list">
			<?php $this->render_part( 'imagify-banner-benefits' ); ?>
			<a class="wpr-imagifyBanner-learnMore" data-mixpanel-button="imagify banner learn more" href="https://imagify.io/" target="_blank" rel="noopener noreferrer">
				<img src="<?php echo esc_url( WP_ROCKET_ASSETS_IMG_URL . 'wpr-info.svg' ); ?>" alt="" width="20" height="20">
				<?php esc_html_e( 'Learn more', 'rocket' ); ?>
				<span class="screen-reader-text"><?php esc_html_e( '(opens in a new tab)', 'rocket' ); ?></span>
			</a>
		</div>

		<div class="wpr-imagifyBanner-cta">
			<img class="wpr-imagifyBanner-image" src="<?php echo esc_url( WP_ROCKET_ASSETS_IMG_URL . 'wpr-imagify-before-after.png' ); ?>" alt="<?php esc_attr_e( 'Comparison between an unoptimized 1.4MB image and the same image optimized with Imagify at 0.4MB', 'rocket' ); ?>" width="262" height="137">
			<?php if ( ! empty( $rocket_button_url ) ) : ?>
				<a class="wpr-button wpr-imagifyBanner-button" href="<?php echo esc_url( $rocket_button_url ); ?>"><?php echo esc_html( $rocket_button_text ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</div>
