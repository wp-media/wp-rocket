<?php
/**
 * Sidebar template.
 *
 * @since 3.0
 */

defined( 'ABSPATH' ) || exit;

?>

<h3 class="wpr-title2"><?php esc_html_e( 'How to correctly measure your website\'s loading time', 'rocket' ); ?></h3>

<div class="wpr-Sidebar-notice">
	<p>
		<?php esc_html_e( 'Check our tutorial and learn how to measure the speed of your site.', 'rocket' ); ?>
	</p>
	<a class="wpr-Sidebar-notice-link" href="https://docs.wp-rocket.me/article/100-testing-your-site-with-and-without-wp-rocket/?utm_source=wp_plugin&utm_medium=wp_rocket" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Read our guide', 'rocket' ); ?></a>
</div>

<div class="wpr-Sidebar-notice">
	<p>
		<?php esc_html_e( 'Learn about optimal WP Rocket settings for mobile.', 'rocket' ); ?>
	</p>
	<a class="wpr-Sidebar-notice-link" href="https://docs.wp-rocket.me/article/1259-mobile-cache-how-to-apply-the-best-mobile-settings-to-increase-your-page-speed/?utm_source=wp_plugin&utm_medium=wp_rocket" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Read our guide', 'rocket' ); ?></a>
</div>

<div class="wpr-Sidebar-notice">
	<p>
		<?php esc_html_e( 'Test and Improve Google Core Web Vitals for WordPress.', 'rocket' ); ?>
	</p>
	<a class="wpr-Sidebar-notice-link" href="https://wp-rocket.me/blog/core-web-vitals-wordpress/?utm_source=wp_plugin&utm_medium=wp_rocket" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Read more', 'rocket' ); ?></a>
</div>

<?php
/**
 * Fires before any sidebar content is rendered.
 *
 * This hook is used to display widgets like the Global Score widget
 * on all WP Rocket admin pages.
 *
 * @since 3.17
 */
do_action( 'rocket_before_sidebar_content' );

/**
 * Fires after sidebar content before quick actions (deprecated).
 *
 * @deprecated 3.17 Use 'rocket_before_sidebar_content' instead.
 */
do_action( 'rocket_after_sidebar_content' );
?>

<?php $this->render_part( 'quick-actions' ); ?>

<?php if ( ! get_rocket_option( 'cache_logged_user', 0 ) ) : ?>
<div class="wpr-Sidebar-info">
	<i class="wpr-icon-information2"></i>
	<h4><?php esc_html_e( 'You have not activated logged-in user cache.', 'rocket' ); ?></h4>
	<p><?php esc_html_e( 'Use a private browser to check your website\'s speed and visual appearance.', 'rocket' ); ?></p>
</div>
<?php endif; ?>
<?php
$this->render_part( 'documentation' );
