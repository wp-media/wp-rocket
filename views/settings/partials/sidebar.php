<?php
/**
 * Sidebar template.
 *
 * @since 3.0
 */

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );
?>

<h3 class="wpr-Sidebar-title wpr-title2"><?php _e( 'How to correctly measure your website’s loading time', 'rocket' ); ?></h3>
<div class="wpr-Sidebar-notice">
	<p><?php _e( 'Learn how to use best practices to correctly measure your site\'s performance.', 'rocket' ); ?></p>
	<a href="<?php echo esc_url( __( 'https://wp-rocket.me/blog/correctly-measure-websites-page-load-time/?utm_source=wp_plugin&utm_medium=wp_rocket', 'rocket' ) ); ?>" target="_blank" class="wpr-Sidebar-notice-link"><?php _e( 'Read our guide', 'rocket' ); ?></a>
</div>
<div class="wpr-Sidebar-notice">
	<p><?php _e( 'Why Google PageSpeed grade should not matter', 'rocket' ); ?></p>
	<a href="<?php echo esc_url( __( 'https://wp-rocket.me/blog/the-truth-about-google-pagespeed-insights/?utm_source=wp_plugin&utm_medium=wp_rocket', 'rocket' ) ); ?>" target="_blank" class="wpr-Sidebar-notice-link"><?php _e( 'Read more', 'rocket' ); ?></a>
</div>
<?php if ( ! get_rocket_option( 'cache_logged_user', 0 ) ) : ?>
<div class="wpr-Sidebar-info">
	<i class="wpr-icon-information2"></i>
	<h4><?php _e( 'You have not activated logged-in user cache.', 'rocket' ); ?></h4>
	<p><?php _e( 'Use a private browser to check your website\'s speed and visual appearance.', 'rocket' ); ?></p>
</div>
<?php endif; ?>
<?php
$this->render_part( 'documentation' );

