<?php
/**
 * Imagify banner, displayed at the top of the Image & Media page while Imagify is not enabled.
 *
 * The content is static placeholder content from the design.
 *
 * @since 3.26
 *
 * @param array $data {
 *     Banner data.
 *
 *     @type string $title       Section title.
 *     @type mixed  $plugin_data Imagify plugin data from WordPress.org.
 * }
 */

defined( 'ABSPATH' ) || exit;

$rocket_installed    = \Imagify_Partner::is_imagify_installed();
$rocket_plugin       = plugin_basename( \Imagify_Partner::get_imagify_path() );
// Installed but inactive: use the standard WordPress activation link, the partner install flow is not available once an API key is saved.
$rocket_activate_url = current_user_can( 'activate_plugins' )
	? wp_nonce_url( self_admin_url( 'plugins.php?action=activate&plugin=' . rawurlencode( $rocket_plugin ) ), 'activate-plugin_' . $rocket_plugin )
	: '';

$rocket_button_text = $rocket_installed ? __( 'Activate', 'rocket' ) : __( 'Install', 'rocket' );
$rocket_button_url  = $rocket_installed ? $rocket_activate_url : ( new \Imagify_Partner( 'wp-rocket' ) )->get_post_install_url();
?>

<div class="wpr-optionHeader">
	<h3 class="wpr-title2"><?php echo esc_html( $data['title'] ); ?></h3>
</div>

<div class="wpr-imagifyBanner">
	<div class="wpr-imagifyBanner-header">
		<div class="wpr-imagifyBanner-headerText">
			<p class="wpr-imagifyBanner-title"><?php esc_html_e( 'Give your website an extra speed boost!', 'rocket' ); ?></p>
			<p class="wpr-imagifyBanner-description"><?php esc_html_e( 'Images can account for 50% of your loading time.', 'rocket' ); ?></p>
		</div>
		<img class="wpr-imagifyBanner-logo" src="<?php echo esc_url( WP_ROCKET_ASSETS_IMG_URL . 'wpr-imagify-logo.svg' ); ?>" alt="Imagify" width="109" height="20">
	</div>

	<div class="wpr-imagifyBanner-content">
		<div class="wpr-imagifyBanner-list">
			<?php $this->render_part( 'imagify-banner-benefits' ); ?>
			<a class="wpr-imagifyBanner-learnMore" href="https://imagify.io/" target="_blank" rel="noopener noreferrer">
				<img src="<?php echo esc_url( WP_ROCKET_ASSETS_IMG_URL . 'wpr-info.svg' ); ?>" alt="" width="20" height="20">
				<?php esc_html_e( 'Learn more', 'rocket' ); ?>
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
