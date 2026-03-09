<?php
/**
 * Sidebar template.
 *
 * @since 3.0
 */

defined( 'ABSPATH' ) || exit;

?>

<?php
/**
 * Fires after sidebar content before quick actions
 *
 * This hook is used to display widgets like the Global Score widget
 * on all WP Rocket admin pages.
 *
 * @since 3.17
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
