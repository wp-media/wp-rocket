<?php
/**
 * Sidebar template.
 *
 * @since 3.0
 */

defined( 'ABSPATH' ) || exit;

?>

<h3 class="wpr-Sidebar-title wpr-title2"><?php esc_html_e( 'How to correctly measure your website’s loading time', 'rocket' ); ?></h3>
<div class="wpr-Sidebar-notice">
	<p><?php esc_html_e( 'Check our tutorial and learn how to measure the speed of your site.', 'rocket' ); ?></p>
	<?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript ?>
	<script src="https://fast.wistia.com/embed/medias/j042jylrre.jsonp" async></script><img src="<?php echo esc_url( WP_ROCKET_ASSETS_IMG_URL . 'play-alt.svg' ); ?>" width="10" height="10" alt=""> <span class="wpr-tutorial-link wpr-Sidebar-notice-link wistia_embed wistia_async_j042jylrre popover=true popoverContent=link" style="display:inline;position:relative"><?php esc_html_e( 'Watch the video', 'rocket' ); ?></span>
</div>
<div class="wpr-Sidebar-notice">
	<p><?php esc_html_e( 'Learn how to use best practices to correctly measure your site\'s performance.', 'rocket' ); ?></p>
	<a href="<?php echo esc_url( __( 'https://wp-rocket.me/blog/correctly-measure-websites-page-load-time/?utm_source=wp_plugin&utm_medium=wp_rocket', 'rocket' ) ); ?>" target="_blank" class="wpr-Sidebar-notice-link"><?php esc_html_e( 'Read our guide', 'rocket' ); ?></a>
</div>
<div class="wpr-Sidebar-notice">
	<p><?php esc_html_e( 'Why Google PageSpeed grade should not matter', 'rocket' ); ?></p>
	<a href="<?php echo esc_url( __( 'https://wp-rocket.me/blog/the-truth-about-google-pagespeed-insights/?utm_source=wp_plugin&utm_medium=wp_rocket', 'rocket' ) ); ?>" target="_blank" class="wpr-Sidebar-notice-link"><?php esc_html_e( 'Read more', 'rocket' ); ?></a>
</div>
<?php if ( ! get_rocket_option( 'cache_logged_user', 0 ) ) : ?>
<div class="wpr-Sidebar-info">
	<i class="wpr-icon-information2"></i>
	<h4><?php esc_html_e( 'You have not activated logged-in user cache.', 'rocket' ); ?></h4>
	<p><?php esc_html_e( 'Use a private browser to check your website\'s speed and visual appearance.', 'rocket' ); ?></p>
</div>
<?php endif; ?>
<?php
$this->render_part( 'documentation' );
